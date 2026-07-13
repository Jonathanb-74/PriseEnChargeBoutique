<?php

namespace App\Http\Controllers;

use App\Models\IntakePhoto;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IntakePhotoController extends Controller
{
    public function __invoke(IntakePhoto $intakePhoto): StreamedResponse
    {
        $this->authorize('view', $intakePhoto->intake);

        $disk = Storage::disk($intakePhoto->disk);

        abort_unless($disk->exists($intakePhoto->path), 404);

        return $disk->response($intakePhoto->path, $intakePhoto->original_name, [
            'Content-Type' => $intakePhoto->mime_type,
        ]);
    }
}
