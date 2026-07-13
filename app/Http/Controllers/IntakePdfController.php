<?php

namespace App\Http\Controllers;

use App\Models\Intake;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class IntakePdfController extends Controller
{
    public function __invoke(Intake $intake): Response
    {
        $this->authorize('downloadPdf', $intake);

        $intake->load(['client', 'machine', 'status', 'technician', 'createdBy', 'staffSignedBy']);

        $disk = Storage::disk('local');

        $pdf = Pdf::loadView('pdf.intake', [
            'intake' => $intake,
            'clientSignaturePath' => $intake->client_signature_path && $disk->exists($intake->client_signature_path)
                ? $disk->path($intake->client_signature_path) : null,
            'staffSignaturePath' => $intake->staff_signature_path && $disk->exists($intake->staff_signature_path)
                ? $disk->path($intake->staff_signature_path) : null,
            'intakeTermsText' => Setting::get(Setting::INTAKE_TERMS_TEXT, ''),
        ]);

        return $pdf->download("{$intake->reference}.pdf");
    }
}
