<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['machine_id', 'disk', 'path', 'original_name', 'mime_type', 'size'])]
class MachinePhoto extends Model
{
    /**
     * @return BelongsTo<Machine, $this>
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function viewUrl(): string
    {
        return route('machine-photos.show', $this);
    }
}
