<?php

namespace App\Livewire\Intakes;

use App\Models\Intake;
use App\Models\Status;
use App\Models\User;
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

    #[Url(history: true)]
    public string $statusId = '';

    #[Url(history: true)]
    public string $clientType = '';

    #[Url(history: true)]
    public string $technicianId = '';

    #[Url(history: true)]
    public string $dateFrom = '';

    #[Url(history: true)]
    public string $dateTo = '';

    #[Url(as: 'open', history: true)]
    public bool $openOnly = false;

    public function mount(): void
    {
        $this->authorize('viewAny', Intake::class);
    }

    public function updating(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'statusId', 'clientType', 'technicianId', 'dateFrom', 'dateTo', 'openOnly']);
    }

    public function render()
    {
        $intakes = Intake::query()
            ->with(['client', 'machine', 'status', 'technician'])
            ->when($this->search, fn ($query) => $query->search($this->search))
            ->when($this->statusId, fn ($query) => $query->where('status_id', $this->statusId))
            ->when($this->clientType, fn ($query) => $query->clientType($this->clientType))
            ->when($this->technicianId, fn ($query) => $query->where('technician_id', $this->technicianId))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->openOnly, fn ($query) => $query->whereHas('status', fn ($q) => $q->where('is_final', false)))
            ->latest()
            ->paginate(15);

        return view('livewire.intakes.index', [
            'intakes' => $intakes,
            'statuses' => Status::query()->orderBy('sort_order')->get(),
            'technicians' => User::query()->assignable()->orderBy('name')->get(),
        ]);
    }
}
