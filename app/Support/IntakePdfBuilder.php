<?php

namespace App\Support;

use App\Models\Intake;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfInstance;
use Illuminate\Support\Facades\Storage;

class IntakePdfBuilder
{
    /**
     * Build the intake PDF. Set $includePassword to false for the client-facing copy —
     * the machine password must never leave the workshop's internal document.
     */
    public static function build(Intake $intake, bool $includePassword = true): PdfInstance
    {
        $intake->loadMissing(['client', 'machine', 'status', 'technician', 'createdBy', 'staffSignedBy', 'photos']);

        $disk = Storage::disk('local');

        return Pdf::loadView('pdf.intake', [
            'intake' => $intake,
            'includePassword' => $includePassword,
            'clientSignaturePath' => $intake->client_signature_path && $disk->exists($intake->client_signature_path)
                ? $disk->path($intake->client_signature_path) : null,
            'staffSignaturePath' => $intake->staff_signature_path && $disk->exists($intake->staff_signature_path)
                ? $disk->path($intake->staff_signature_path) : null,
            'issuePhotoPaths' => $intake->photos
                ->filter(fn ($photo) => Storage::disk($photo->disk)->exists($photo->path))
                ->map(fn ($photo) => Storage::disk($photo->disk)->path($photo->path))
                ->all(),
            'intakeTermsText' => Setting::get(Setting::INTAKE_TERMS_TEXT, ''),
            'pdfLogoPath' => Setting::pdfLogoPath(),
            'accentColor' => Setting::accentColor(),
        ]);
    }
}
