<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['intake_id', 'disk', 'path', 'original_name', 'mime_type', 'size'])]
class IntakePhoto extends Model
{
    /**
     * @return BelongsTo<Intake, $this>
     */
    public function intake(): BelongsTo
    {
        return $this->belongsTo(Intake::class);
    }

    public function viewUrl(): string
    {
        return route('intake-photos.show', $this);
    }
}
