<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['intake_id', 'user_id', 'body'])]
class IntakeNote extends Model
{
    /**
     * @return BelongsTo<Intake, $this>
     */
    public function intake(): BelongsTo
    {
        return $this->belongsTo(Intake::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
