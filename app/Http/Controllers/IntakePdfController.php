<?php

namespace App\Http\Controllers;

use App\Models\Intake;
use App\Support\IntakePdfBuilder;
use Symfony\Component\HttpFoundation\Response;

class IntakePdfController extends Controller
{
    public function __invoke(Intake $intake): Response
    {
        $this->authorize('downloadPdf', $intake);

        return IntakePdfBuilder::build($intake, includePassword: true)
            ->download("{$intake->reference}.pdf");
    }
}
