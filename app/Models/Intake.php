<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'reference',
    'client_id',
    'machine_id',
    'status_id',
    'technician_id',
    'created_by',
    'reported_issue',
    'client_signature_path',
    'client_signature_name',
    'client_signed_at',
    'staff_signature_path',
    'staff_signed_by',
    'staff_signed_at',
])]
class Intake extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'client_signed_at' => 'datetime',
            'staff_signed_at' => 'datetime',
        ];
    }

    public static function generateReference(): string
    {
        $year = now()->year;

        $count = static::withTrashed()
            ->whereYear('created_at', $year)
            ->count();

        return sprintf('PEC-%d-%04d', $year, $count + 1);
    }

    public function isFullySigned(): bool
    {
        return $this->client_signature_path !== null && $this->staff_signature_path !== null;
    }

    /**
     * Decode a canvas data URL (data:image/png;base64,...) and store it as a PNG on the
     * private disk, returning the storage path. Used for both client and staff signatures.
     */
    public static function storeSignatureImage(string $dataUrl, int|string $intakeId, string $type): ?string
    {
        if (! str_starts_with($dataUrl, 'data:image/png;base64,')) {
            return null;
        }

        $binary = base64_decode(substr($dataUrl, strlen('data:image/png;base64,')));

        if ($binary === false) {
            return null;
        }

        $path = "signatures/{$intakeId}/{$type}-".Str::random(10).'.png';

        Storage::disk('local')->put($path, $binary);

        return $path;
    }

    /**
     * Copy an existing signature file (e.g. a user's pre-registered signature) into this
     * intake's own signature files, so it stays intact even if the source is later replaced.
     */
    public static function copySignatureFile(string $sourcePath, int|string $intakeId, string $type): ?string
    {
        if (! Storage::disk('local')->exists($sourcePath)) {
            return null;
        }

        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'png';
        $path = "signatures/{$intakeId}/{$type}-".Str::random(10).".{$extension}";

        Storage::disk('local')->put($path, Storage::disk('local')->get($sourcePath));

        return $path;
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<Machine, $this>
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * @return BelongsTo<Status, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function staffSignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_signed_by');
    }

    /**
     * @return HasMany<IntakeNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(IntakeNote::class)->latest();
    }

    /**
     * @return HasMany<IntakeStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(IntakeStatusHistory::class)->latest('changed_at');
    }

    /**
     * @return HasMany<ClientNotification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(ClientNotification::class)->latest();
    }

    /**
     * Photos of the reported issue itself (e.g. physical damage), specific to this intake —
     * as opposed to Machine::photos(), which are permanent identification photos of the
     * machine (label, serial number) that persist across every intake for that machine.
     *
     * @return HasMany<IntakePhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(IntakePhoto::class)->latest();
    }

    /**
     * @param  Builder<Intake>  $query
     * @return Builder<Intake>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $query) use ($term) {
            $query->where('reference', 'like', "%{$term}%")
                ->orWhereHas('client', function (Builder $query) use ($term) {
                    $query->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('company_name', 'like', "%{$term}%");
                })
                ->orWhereHas('machine', function (Builder $query) use ($term) {
                    $query->where('serial_number', 'like', "%{$term}%")
                        ->orWhere('brand', 'like', "%{$term}%")
                        ->orWhere('model', 'like', "%{$term}%");
                });
        });
    }

    /**
     * @param  Builder<Intake>  $query
     * @return Builder<Intake>
     */
    public function scopeClientType(Builder $query, string $type): Builder
    {
        return $query->whereHas('client', fn (Builder $query) => $query->where('type', $type));
    }
}
