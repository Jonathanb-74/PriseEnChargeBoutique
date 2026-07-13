<?php

namespace App\Http\Controllers;

use App\Models\Intake;
use App\Support\IntakePdfBuilder;
use Symfony\Component\HttpFoundation\Response;

class IntakeClientPdfController extends Controller
{
    public function __invoke(Intake $intake): Response
    {
        $this->authorize('downloadPdf', $intake);

        return IntakePdfBuilder::build($intake, includePassword: false)
            ->download("{$intake->reference}-client.pdf");
    }
}
