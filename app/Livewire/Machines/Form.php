<?php

namespace App\Livewire\Machines;

use App\Models\Client;
use App\Models\Machine;
use App\Models\MachinePhoto;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Form extends Component
{
    use WithFileUploads;

    public ?Machine $machine = null;

    public Client $client;

    public string $brand = '';

    public string $model = '';

    public string $serial_number = '';

    public string $password = '';

    public string $notes = '';

    public bool $showPassword = false;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $newPhotos = [];

    public function mount(?Machine $machine = null, ?Client $client = null): void
    {
        if ($machine?->exists) {
            $this->authorize('update', $machine);
            $this->machine = $machine->load('photos');
            $this->client = $machine->client;
            $this->brand = $machine->brand;
            $this->model = $machine->model;
            $this->serial_number = $machine->serial_number ?? '';
            $this->password = $machine->password ?? '';
            $this->notes = $machine->notes ?? '';
        } else {
            $this->authorize('create', Machine::class);
            abort_if($client === null, 400, 'Client requis.');
            $this->client = $client;
        }
    }

    protected function rules(): array
    {
        return [
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:150'],
            'password' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'newPhotos' => ['array', 'max:10'],
            'newPhotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function save(): mixed
    {
        $data = $this->validate();
        $photos = $data['newPhotos'] ?? [];
        unset($data['newPhotos']);

        if ($this->machine) {
            $this->machine->update($data);
        } else {
            $data['client_id'] = $this->client->id;
            $this->machine = Machine::create($data);
        }

        foreach ($photos as $photo) {
            $path = $photo->store('machines/'.$this->machine->id, 'local');

            MachinePhoto::create([
                'machine_id' => $this->machine->id,
                'disk' => 'local',
                'path' => $path,
                'original_name' => $photo->getClientOriginalName(),
                'mime_type' => $photo->getMimeType(),
                'size' => $photo->getSize(),
            ]);
        }

        $this->newPhotos = [];

        session()->flash('status', 'Machine enregistrée.');

        return $this->redirect(route('clients.show', $this->client), navigate: true);
    }

    public function deletePhoto(MachinePhoto $photo): void
    {
        $this->authorize('update', $this->machine);

        Storage::disk($photo->disk)->delete($photo->path);
        $photo->delete();

        $this->machine->refresh()->load('photos');
    }

    public function render()
    {
        return view('livewire.machines.form');
    }
}
