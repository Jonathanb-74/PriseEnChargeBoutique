<?php

namespace App\Livewire\EmailTemplates;

use App\Models\EmailTemplate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $subject = '';

    public string $body = '';

    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('viewAny', EmailTemplate::class);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'is_active' => ['boolean'],
        ];
    }

    public function edit(EmailTemplate $template): void
    {
        $this->authorize('update', $template);

        $this->editingId = $template->id;
        $this->name = $template->name;
        $this->subject = $template->subject;
        $this->body = $template->body;
        $this->is_active = $template->is_active;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'name', 'subject', 'body', 'is_active']);
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            $template = EmailTemplate::findOrFail($this->editingId);
            $this->authorize('update', $template);
            $template->update($data);
        } else {
            $this->authorize('create', EmailTemplate::class);
            $data['key'] = Str::slug($this->name, '_').'_'.uniqid();
            EmailTemplate::create($data);
        }

        $this->cancelEdit();
    }

    public function delete(EmailTemplate $template): void
    {
        $this->authorize('delete', $template);

        $template->delete();
    }

    public function render()
    {
        return view('livewire.email-templates.index', [
            'templates' => EmailTemplate::query()->orderBy('name')->get(),
        ]);
    }
}
