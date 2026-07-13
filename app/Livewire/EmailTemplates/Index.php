<?php

namespace App\Livewire\EmailTemplates;

use App\Models\EmailTemplate;
use App\Models\Setting;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $subject = '';

    public string $email_title = '';

    public string $body = '';

    public bool $is_active = true;

    public bool $attach_pdf = false;

    public string $emailSignature = '';

    public function mount(): void
    {
        $this->authorize('viewAny', EmailTemplate::class);

        $this->emailSignature = Setting::get(Setting::EMAIL_SIGNATURE, '');
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'subject' => ['required', 'string', 'max:255'],
            'email_title' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
            'is_active' => ['boolean'],
            'attach_pdf' => ['boolean'],
        ];
    }

    public function edit(EmailTemplate $template): void
    {
        $this->authorize('update', $template);

        $this->editingId = $template->id;
        $this->name = $template->name;
        $this->subject = $template->subject;
        $this->email_title = $template->email_title ?? '';
        $this->body = $template->body;
        $this->is_active = $template->is_active;
        $this->attach_pdf = $template->attach_pdf;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'name', 'subject', 'email_title', 'body', 'is_active', 'attach_pdf']);
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

    public function saveSignature(): void
    {
        $this->authorize('viewAny', EmailTemplate::class);

        $this->validate([
            'emailSignature' => ['nullable', 'string', 'max:2000'],
        ]);

        Setting::set(Setting::EMAIL_SIGNATURE, $this->emailSignature ?: null);

        session()->flash('status', 'Signature enregistrée.');
    }

    public function render()
    {
        return view('livewire.email-templates.index', [
            'templates' => EmailTemplate::query()->orderBy('name')->get(),
        ]);
    }
}
