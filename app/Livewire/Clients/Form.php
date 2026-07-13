<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public ?Client $client = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $type = 'particulier';

    public string $company_name = '';

    public string $email = '';

    public string $phone = '';

    public string $address_line1 = '';

    public string $address_line2 = '';

    public string $postal_code = '';

    public string $city = '';

    public ?string $code_client = null;

    public string $siret = '';

    public bool $actif = true;

    public function mount(?Client $client = null): void
    {
        if ($client?->exists) {
            $this->authorize('update', $client);
            $this->client = $client;
            $this->first_name = $client->first_name;
            $this->last_name = $client->last_name;
            $this->type = $client->type instanceof \BackedEnum ? $client->type->value : $client->type;
            $this->company_name = $client->company_name ?? '';
            $this->email = $client->email ?? '';
            $this->phone = $client->phone ?? '';
            $this->address_line1 = $client->address_line1 ?? '';
            $this->address_line2 = $client->address_line2 ?? '';
            $this->postal_code = $client->postal_code ?? '';
            $this->city = $client->city ?? '';
            $this->code_client = $client->code_client;
            $this->siret = $client->siret ?? '';
            $this->actif = $client->actif;
        } else {
            $this->authorize('create', Client::class);
        }
    }

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:pro,particulier'],
            'company_name' => ['nullable', 'required_if:type,pro', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address_line1' => ['nullable', 'string', 'max:150'],
            'address_line2' => ['nullable', 'string', 'max:150'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'code_client' => [
                'nullable', 'string', 'max:50',
                Rule::unique('clients', 'code_client')->ignore($this->client?->id),
            ],
            'siret' => ['nullable', 'string', 'max:20'],
            'actif' => ['boolean'],
        ];
    }

    public function save()
    {
        if ($this->code_client === '') {
            $this->code_client = null;
        }

        $data = $this->validate();

        if ($this->client) {
            $this->client->update($data);
        } else {
            $this->client = Client::create($data);
        }

        session()->flash('status', 'Client enregistré.');

        return $this->redirect(route('clients.show', $this->client), navigate: true);
    }

    public function render()
    {
        return view('livewire.clients.form');
    }
}
