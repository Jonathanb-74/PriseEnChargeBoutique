<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'name', 'subject', 'email_title', 'body', 'is_active', 'attach_pdf'])]
class EmailTemplate extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'attach_pdf' => 'boolean',
        ];
    }

    public function render(array $replacements): array
    {
        $subject = $this->subject;
        $body = $this->body;

        foreach ($replacements as $key => $value) {
            $subject = str_replace('{{'.$key.'}}', $value, $subject);
            $body = str_replace('{{'.$key.'}}', $value, $body);
        }

        return [$subject, $body];
    }
}
