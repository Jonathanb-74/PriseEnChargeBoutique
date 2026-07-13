<?php

namespace App\Mail;

use App\Models\Intake;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IntakeCreated extends Mailable
{
    use Queueable, SerializesModels;

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
}
