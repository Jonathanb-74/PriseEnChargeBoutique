<?php

namespace App\Mail;

use App\Models\ClientNotification;
use App\Models\Intake;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IntakeCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ?int $notificationId = null;

    public function __construct(public Intake $intake) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Prise en charge {$this->intake->reference} enregistrée",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.intake-created',
        );
    }

    public function failed(\Throwable $exception): void
    {
        if ($this->notificationId) {
            ClientNotification::whereKey($this->notificationId)->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }
    }
}
