<?php

namespace App\Livewire\Statuses;

use App\Models\Status;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public ?int $editingId = null;

    public string $label = '';

    public string $color = '#3b82f6';

    public bool $is_default = false;

    public bool $is_final = false;

    public function mount(): void
    {
        $this->authorize('viewAny', Status::class);
    }

    protected function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:100'],
            'color' => ['required', 'string', 'max:20'],
            'is_default' => ['boolean'],
            'is_final' => ['boolean'],
        ];
    }

    public function edit(Status $status): void
    {
        $this->authorize('update', $status);

        $this->editingId = $status->id;
        $this->label = $status->label;
        $this->color = $status->color;
        $this->is_default = $status->is_default;
        $this->is_final = $status->is_final;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'label', 'color', 'is_default', 'is_final']);
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->is_default) {
            Status::query()->where('id', '!=', $this->editingId)->update(['is_default' => false]);
        }

        if ($this->editingId) {
            $status = Status::findOrFail($this->editingId);
            $this->authorize('update', $status);
            $data['slug'] = $status->slug;
            $status->update($data);
        } else {
            $this->authorize('create', Status::class);
            $data['slug'] = Str::slug($this->label, '_');
            $data['sort_order'] = (Status::max('sort_order') ?? -1) + 1;
            Status::create($data);
        }

        $this->cancelEdit();
    }

    public function moveStatus(int $draggedId, int $targetId): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        if ($draggedId === $targetId) {
            return;
        }

        $ids = Status::query()->orderBy('sort_order')->pluck('id')->all();

        $from = array_search($draggedId, $ids, true);
        $to = array_search($targetId, $ids, true);

        if ($from === false || $to === false) {
            return;
        }

        array_splice($ids, $from, 1);
        array_splice($ids, $to, 0, [$draggedId]);

        foreach ($ids as $index => $id) {
            Status::whereKey($id)->update(['sort_order' => $index]);
        }
    }

    public function delete(Status $status): void
    {
        $this->authorize('delete', $status);

        if ($status->intakes()->exists()) {
            session()->flash('error', 'Ce statut est utilisé par des prises en charge, il ne peut pas être supprimé.');

            return;
        }

        $status->delete();
    }

    public function render()
    {
        return view('livewire.statuses.index', [
            'statuses' => Status::query()->orderBy('sort_order')->get(),
        ]);
    }
}
