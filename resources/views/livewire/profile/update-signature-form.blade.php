<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public ?string $signatureData = null;

    public $signatureFile = null;

    public function saveDrawn(): void
    {
        $this->validate([
            'signatureData' => ['required', 'string', 'starts_with:data:image/png;base64,'],
        ]);

        $binary = base64_decode(substr($this->signatureData, strlen('data:image/png;base64,')));

        if ($binary === false) {
            $this->addError('signatureData', 'Signature invalide, veuillez réessayer.');

            return;
        }

        $this->storeSignature($binary, 'png', 'drawn');

        $this->signatureData = null;

        session()->flash('status', 'Signature enregistrée.');
    }

    public function saveUploaded(): void
    {
        $this->validate([
            'signatureFile' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
        ]);

        $extension = $this->signatureFile->getClientOriginalExtension() ?: 'png';

        $this->storeSignature(file_get_contents($this->signatureFile->getRealPath()), $extension, 'uploaded');

        $this->signatureFile = null;

        session()->flash('status', 'Signature enregistrée.');
    }

    public function removeSignature(): void
    {
        $user = Auth::user();

        if ($user->signature_path) {
            Storage::disk('local')->delete($user->signature_path);
        }

        $user->update([
            'signature_path' => null,
            'signature_type' => null,
            'signature_updated_at' => null,
        ]);

        session()->flash('status', 'Signature supprimée.');
    }

    protected function storeSignature(string $binary, string $extension, string $type): void
    {
        $user = Auth::user();

        if ($user->signature_path) {
            Storage::disk('local')->delete($user->signature_path);
        }

        $path = "signatures/users/{$user->id}-".Str::random(10).".{$extension}";

        Storage::disk('local')->put($path, $binary);

        $user->update([
            'signature_path' => $path,
            'signature_type' => $type,
            'signature_updated_at' => now(),
        ]);
    }

    public function with(): array
    {
        return [
            'signaturePreview' => Auth::user()->signaturePreviewDataUri(),
        ];
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Signature
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Enregistrez votre signature une fois : elle sera appliquée automatiquement à la création d'une prise en charge, il ne restera plus que le client à faire signer.
        </p>
    </header>

    <div class="mt-6 space-y-6">
        @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('status') }}</div>
        @endif

        @if ($signaturePreview)
            <div>
                <x-input-label value="Signature actuelle" />
                <div class="mt-2 flex items-center gap-4">
                    <img src="{{ $signaturePreview }}" class="h-20 border border-gray-200 dark:border-gray-700 rounded-md bg-white p-2">
                    <button type="button" wire:click="removeSignature" wire:confirm="Supprimer votre signature enregistrée ?" class="text-sm text-red-600 dark:text-red-400">
                        Supprimer
                    </button>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <form wire:submit="saveDrawn" class="space-y-2">
                <x-signature-pad property="signatureData" label="Dessiner une signature" />
                <x-input-error :messages="$errors->get('signatureData')" class="mt-2" />
                <x-primary-button>{{ $signaturePreview ? 'Remplacer' : 'Enregistrer' }}</x-primary-button>
            </form>

            <div class="space-y-2">
                <x-input-label for="signatureFile" value="Ou importer une image" />
                <input type="file" id="signatureFile" wire:model="signatureFile" accept="image/png,image/jpeg"
                    class="block w-full text-sm text-gray-600 dark:text-gray-300">
                <div wire:loading wire:target="signatureFile" class="text-xs text-gray-500 dark:text-gray-400">Chargement…</div>
                @if ($signatureFile)
                    <img src="{{ $signatureFile->temporaryUrl() }}" class="h-16 border border-gray-200 dark:border-gray-700 rounded-md bg-white p-1">
                @endif
                <x-input-error :messages="$errors->get('signatureFile')" class="mt-2" />
                <p class="text-xs text-gray-500 dark:text-gray-400">PNG ou JPG, 1 Mo maximum.</p>
                <x-primary-button type="button" wire:click="saveUploaded">{{ $signaturePreview ? 'Remplacer' : 'Enregistrer' }}</x-primary-button>
            </div>
        </div>
    </div>
</section>
