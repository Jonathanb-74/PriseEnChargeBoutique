<?php

namespace App\Livewire\Intakes;

use App\Mail\ClientNotificationMail;
use App\Models\ClientNotification;
use App\Models\EmailTemplate;
use App\Models\Intake;
use App\Models\IntakeNote;
use App\Models\IntakePhoto;
use App\Models\IntakeStatusHistory;
use App\Models\MachinePhoto;
use App\Models\Setting;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Show extends Component
{
    use WithFileUploads;

    public Intake $intake;

    public string $newNote = '';

    public int $statusId;

    public ?int $technicianId = null;

    public bool $showMachinePassword = false;

    public ?int $selectedTemplateId = null;

    public string $notif_subject = '';

    public string $notif_body = '';

    public string $notif_cc = '';

    public string $notif_cc_mode = 'bcc';

    public ?string $clientSignatureData = null;

    public string $clientSignatureName = '';

    public ?string $staffSignatureData = null;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $newPhotos = [];

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $newIssuePhotos = [];

    public function mount(Intake $intake): void
    {
        $this->authorize('view', $intake);

        $this->intake = $intake;
        $this->statusId = $intake->status_id;
        $this->technicianId = $intake->technician_id;
        $this->clientSignatureName = $intake->client_signature_name ?? $intake->client->full_name;
    }

    protected function loadIntake(): void
    {
        $this->intake = $this->intake->fresh([
            'client', 'machine.photos', 'status', 'technician', 'createdBy',
            'notes.user', 'statusHistories.status', 'statusHistories.changedBy',
            'notifications.sentBy', 'photos',
        ]);
    }

    protected function guardNotFinal(): void
    {
        abort_if($this->intake->status->is_final, 403, "Cette prise en charge est clôturée. Rouvrez-la (changez son statut) avant de la modifier.");
    }

    public function addNote(): void
    {
        $this->authorize('update', $this->intake);
        $this->guardNotFinal();

        $this->validate(['newNote' => ['required', 'string', 'max:2000']]);

        IntakeNote::create([
            'intake_id' => $this->intake->id,
            'user_id' => Auth::id(),
            'body' => $this->newNote,
        ]);

        $this->newNote = '';
        $this->loadIntake();
    }

    public function changeStatus(): void
    {
        $this->authorize('update', $this->intake);

        $this->validate(['statusId' => ['required', 'exists:statuses,id']]);

        if ($this->statusId !== $this->intake->status_id) {
            $this->intake->update(['status_id' => $this->statusId]);

            IntakeStatusHistory::create([
                'intake_id' => $this->intake->id,
                'status_id' => $this->statusId,
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);

            session()->flash('status', 'Statut mis à jour.');
        }

        $this->loadIntake();
    }

    public function assignTechnician(): void
    {
        $this->authorize('update', $this->intake);
        $this->guardNotFinal();

        $this->validate([
            'technicianId' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where(function ($query) {
                    $query->where('is_assignable', true)->orWhere('id', $this->intake->technician_id);
                })),
            ],
        ]);

        $this->intake->update(['technician_id' => $this->technicianId]);

        session()->flash('status', 'Technicien assigné.');
        $this->loadIntake();
    }

    public function toggleMachinePassword(): void
    {
        $this->authorize('viewMachinePassword', $this->intake);

        $this->showMachinePassword = ! $this->showMachinePassword;
    }

    /**
     * A native <input multiple> replaces its whole selection every time the user picks files
     * again, so each batch is validated and stored immediately rather than queued for a
     * separate "save" step (this page has no such step).
     */
    public function updatedNewPhotos(): void
    {
        $this->authorize('update', $this->intake->machine);
        $this->guardNotFinal();

        $this->validateOnly('newPhotos.*', [
            'newPhotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        foreach ($this->newPhotos as $photo) {
            $path = $photo->store('machines/'.$this->intake->machine_id, 'local');

            MachinePhoto::create([
                'machine_id' => $this->intake->machine_id,
                'disk' => 'local',
                'path' => $path,
                'original_name' => $photo->getClientOriginalName(),
                'mime_type' => $photo->getMimeType(),
                'size' => $photo->getSize(),
            ]);
        }

        $this->newPhotos = [];
        $this->loadIntake();
    }

    public function deletePhoto(MachinePhoto $photo): void
    {
        $this->authorize('update', $this->intake->machine);
        $this->guardNotFinal();

        Storage::disk($photo->disk)->delete($photo->path);
        $photo->delete();

        $this->loadIntake();
    }

    /**
     * Photos of the reported issue itself (e.g. physical damage), tied to this intake rather
     * than the machine — unlike updatedNewPhotos() above, which stores permanent machine
     * identification photos (label, serial number).
     */
    public function updatedNewIssuePhotos(): void
    {
        $this->authorize('update', $this->intake);
        $this->guardNotFinal();

        $this->validateOnly('newIssuePhotos.*', [
            'newIssuePhotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        foreach ($this->newIssuePhotos as $photo) {
            $path = $photo->store('intakes/'.$this->intake->id, 'local');

            IntakePhoto::create([
                'intake_id' => $this->intake->id,
                'disk' => 'local',
                'path' => $path,
                'original_name' => $photo->getClientOriginalName(),
                'mime_type' => $photo->getMimeType(),
                'size' => $photo->getSize(),
            ]);
        }

        $this->newIssuePhotos = [];
        $this->loadIntake();
    }

    public function deleteIssuePhoto(IntakePhoto $photo): void
    {
        $this->authorize('update', $this->intake);
        $this->guardNotFinal();

        Storage::disk($photo->disk)->delete($photo->path);
        $photo->delete();

        $this->loadIntake();
    }

    public function saveClientSignature(): void
    {
        $this->authorize('update', $this->intake);
        $this->guardNotFinal();

        $this->validate([
            'clientSignatureName' => ['required', 'string', 'max:150'],
            'clientSignatureData' => ['required', 'string'],
        ]);

        $path = Intake::storeSignatureImage($this->clientSignatureData, $this->intake->id, 'client');

        if (! $path) {
            $this->addError('clientSignatureData', 'Signature invalide, veuillez réessayer.');

            return;
        }

        $this->intake->update([
            'client_signature_path' => $path,
            'client_signature_name' => $this->clientSignatureName,
            'client_signed_at' => now(),
        ]);

        $this->clientSignatureData = null;
        session()->flash('status', 'Signature du client enregistrée.');
        $this->loadIntake();
    }

    public function saveStaffSignature(): void
    {
        $this->authorize('update', $this->intake);
        $this->guardNotFinal();

        $this->validate(['staffSignatureData' => ['required', 'string']]);

        $path = Intake::storeSignatureImage($this->staffSignatureData, $this->intake->id, 'staff');

        if (! $path) {
            $this->addError('staffSignatureData', 'Signature invalide, veuillez réessayer.');

            return;
        }

        $this->intake->update([
            'staff_signature_path' => $path,
            'staff_signed_by' => Auth::id(),
            'staff_signed_at' => now(),
        ]);

        $this->staffSignatureData = null;
        session()->flash('status', "Signature de l'employé enregistrée.");
        $this->loadIntake();
    }

    public function updatedSelectedTemplateId(): void
    {
        if (! $this->selectedTemplateId) {
            return;
        }

        $template = EmailTemplate::find($this->selectedTemplateId);

        if (! $template) {
            return;
        }

        [$subject, $body] = $template->render([
            'reference' => $this->intake->reference,
            'client' => $this->intake->client->full_name,
            'machine' => "{$this->intake->machine->brand} {$this->intake->machine->model}",
            'statut' => $this->intake->status->label,
            'panne' => $this->intake->reported_issue ?: 'Non précisé.',
        ]);

        $this->notif_subject = $subject;
        $this->notif_body = $body;
    }

    public function sendNotification(): void
    {
        $this->authorize('sendClientNotification', $this->intake);
        $this->guardNotFinal();

        $this->validate([
            'notif_subject' => ['required', 'string', 'max:255'],
            'notif_body' => ['required', 'string', 'max:5000'],
            'notif_cc' => ['nullable', 'string'],
            'notif_cc_mode' => ['required', 'in:cc,bcc'],
        ]);

        if (! $this->intake->client->email) {
            $this->addError('notif_subject', "Ce client n'a pas d'adresse email.");

            return;
        }

        $extraRecipients = array_values(array_unique(array_filter(array_map('trim', explode(',', $this->notif_cc)))));

        $cc = $this->notif_cc_mode === 'cc' ? $extraRecipients : [];
        $bcc = $this->notif_cc_mode === 'bcc'
            ? array_values(array_unique(array_merge([Auth::user()->email], $extraRecipients)))
            : [Auth::user()->email];

        $template = $this->selectedTemplateId ? EmailTemplate::find($this->selectedTemplateId) : null;

        $notification = ClientNotification::create([
            'intake_id' => $this->intake->id,
            'template_key' => $template?->key,
            'subject' => $this->notif_subject,
            'recipient_email' => $this->intake->client->email,
            'cc' => $cc,
            'bcc' => $bcc,
            'sent_by' => Auth::id(),
            'status' => 'queued',
        ]);

        $mailable = new ClientNotificationMail($this->notif_subject, $this->notif_body, $template?->attach_pdf ? $this->intake : null, $template?->email_title);
        $mailable->notificationId = $notification->id;

        try {
            Mail::to($this->intake->client->email)->cc($cc)->bcc($bcc)->send($mailable);

            session()->flash('status', "Notification mise en file d'attente pour envoi au client.");
        } catch (\Throwable $e) {
            $notification->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            session()->flash('error', "L'envoi a échoué : ".$e->getMessage());
        }

        $this->reset(['selectedTemplateId', 'notif_subject', 'notif_body', 'notif_cc']);
        $this->loadIntake();
    }

    public function render()
    {
        $this->loadIntake();

        return view('livewire.intakes.show', [
            'statuses' => Status::query()->orderBy('sort_order')->get(),
            'templates' => EmailTemplate::query()->where('is_active', true)->orderBy('name')->get(),
            'technicians' => User::query()
                ->where(function ($query) {
                    $query->where('is_assignable', true)->orWhere('id', $this->intake->technician_id);
                })
                ->orderBy('name')
                ->get(),
            'intakeTermsText' => Setting::get(Setting::INTAKE_TERMS_TEXT, ''),
        ]);
    }
}
