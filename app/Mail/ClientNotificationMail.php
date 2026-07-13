<?php

namespace App\Mail;

use App\Models\ClientNotification;
use App\Models\Intake;
use App\Support\IntakePdfBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ?int $notificationId = null;

    public function __construct(
        public string $subjectLine,
        public string $body,
        public ?Intake $pdfIntake = null,
        public ?string $emailTitle = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
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

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.client-notification',
            with: ['body' => $this->body, 'emailTitle' => $this->emailTitle],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->pdfIntake) {
            return [];
        }

        $intake = $this->pdfIntake;

        return [
            Attachment::fromData(
                fn () => IntakePdfBuilder::build($intake, includePassword: false)->output(),
                "{$intake->reference}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
