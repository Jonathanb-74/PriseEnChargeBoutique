<?php

namespace App\Livewire;

use App\Models\Intake;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $openCount = Intake::query()->whereHas('status', fn ($q) => $q->where('is_final', false))->count();
        $mineCount = Intake::query()->where('technician_id', auth()->id())
            ->whereHas('status', fn ($q) => $q->where('is_final', false))
            ->count();

        $openIntakes = Intake::query()
            ->with(['client', 'machine', 'status'])
            ->whereHas('status', fn ($q) => $q->where('is_final', false))
            ->latest()
            ->limit(15)
            ->get();

        return view('livewire.dashboard', [
            'openCount' => $openCount,
            'mineCount' => $mineCount,
            'openIntakes' => $openIntakes,
        ]);
    }
}
