<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['client_id', 'brand', 'model', 'serial_number', 'password', 'notes'])]
#[Hidden(['password'])]
class Machine extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return HasMany<MachinePhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(MachinePhoto::class);
    }

    /**
     * @return HasMany<Intake, $this>
     */
    public function intakes(): HasMany
    {
        return $this->hasMany(Intake::class);
    }
}
