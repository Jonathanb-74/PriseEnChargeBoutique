<?php

namespace App\Http\Controllers;

use App\Models\MachinePhoto;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MachinePhotoController extends Controller
{
    public function __invoke(MachinePhoto $machinePhoto): StreamedResponse
    {
        $this->authorize('view', $machinePhoto->machine);

        $disk = Storage::disk($machinePhoto->disk);

        abort_unless($disk->exists($machinePhoto->path), 404);

        return $disk->response($machinePhoto->path, $machinePhoto->original_name, [
            'Content-Type' => $machinePhoto->mime_type,
        ]);
    }
}
