<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Client::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(Client $client): void
    {
        $this->authorize('delete', $client);

        $client->delete();

        session()->flash('status', 'Client supprimé.');
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, fn ($query) => $query->search($this->search))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15);

        return view('livewire.clients.index', ['clients' => $clients]);
    }
}
