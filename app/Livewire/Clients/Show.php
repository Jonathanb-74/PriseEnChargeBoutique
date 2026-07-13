<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Client $client;

    public function mount(Client $client): void
    {
        $this->authorize('view', $client);
        $this->client = $client;
    }

    public function render()
    {
        $this->client->load([
            'machines' => fn ($query) => $query->latest(),
            'intakes' => fn ($query) => $query->with(['machine', 'status'])->latest(),
        ]);

        return view('livewire.clients.show');
    }
}
