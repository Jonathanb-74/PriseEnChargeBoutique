<?php

namespace App\Http\Controllers;

use App\Models\Intake;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IntakeSignatureController extends Controller
{
    public function __invoke(Intake $intake, string $type): StreamedResponse
    {
        $this->authorize('view', $intake);

        abort_unless(in_array($type, ['client', 'staff'], true), 404);

        $path = $type === 'client' ? $intake->client_signature_path : $intake->staff_signature_path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
