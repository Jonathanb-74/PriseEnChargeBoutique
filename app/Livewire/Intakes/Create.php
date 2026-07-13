<?php

namespace App\Livewire\Intakes;

use App\Mail\ClientNotificationMail;
use App\Mail\IntakeCreated;
use App\Models\Client;
use App\Models\ClientNotification;
use App\Models\EmailTemplate;
use App\Models\Intake;
use App\Models\IntakePhoto;
use App\Models\IntakeStatusHistory;
use App\Models\Machine;
use App\Models\MachinePhoto;
use App\Models\Setting;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Create extends Component
{
    use WithFileUploads;

    #[Url(as: 'client')]
    public ?int $prefilledClientId = null;

    public string $clientSearch = '';

    public ?int $selectedClientId = null;

    public bool $creatingNewClient = false;

    public string $nc_first_name = '';

    public string $nc_last_name = '';

    public string $nc_type = 'particulier';

    public string $nc_company_name = '';

    public string $nc_email = '';

    public string $nc_phone = '';

    public string $nc_address_line1 = '';

    public string $nc_address_line2 = '';

    public string $nc_postal_code = '';

    public string $nc_city = '';

    public ?int $selectedMachineId = null;

    public bool $creatingNewMachine = true;

    public string $m_brand = '';

    public string $m_model = '';

    public string $m_serial_number = '';

    public string $m_password = '';

    public string $m_notes = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $newPhotos = [];

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $photoQueue = [];

    public string $reported_issue = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $newIssuePhotos = [];

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $issuePhotoQueue = [];

    public string $cc_email = '';

    public ?string $clientSignatureData = null;

    public string $clientSignatureName = '';

    public ?string $staffSignatureData = null;

    public function mount(): void
    {
        $this->authorize('create', Intake::class);

        if ($this->prefilledClientId && $client = Client::find($this->prefilledClientId)) {
            $this->pickClient($client);
        }
    }

    #[Computed]
    public function clientResults()
    {
        if ($this->selectedClientId || mb_strlen($this->clientSearch) < 2) {
            return collect();
        }

        return Client::query()->search($this->clientSearch)->limit(8)->get();
    }

    #[Computed]
    public function selectedClient(): ?Client
    {
        return $this->selectedClientId ? Client::find($this->selectedClientId) : null;
    }

    #[Computed]
    public function clientMachines()
    {
        return $this->selectedClientId
            ? Machine::query()->where('client_id', $this->selectedClientId)->orderByDesc('id')->get()
            : collect();
    }

    public function pickClient(Client $client): void
    {
        $this->selectedClientId = $client->id;
        $this->creatingNewClient = false;
        $this->clientSearch = '';
        $this->selectedMachineId = null;
        $this->creatingNewMachine = $this->clientMachines->isEmpty();
        $this->clientSignatureName = $client->full_name;
    }

    public function clearClient(): void
    {
        $this->selectedClientId = null;
        $this->selectedMachineId = null;
        $this->creatingNewMachine = true;
    }

    public function startNewClient(): void
    {
        $this->creatingNewClient = true;
        $this->selectedClientId = null;
        $this->clientSearch = '';
    }

    public function cancelNewClient(): void
    {
        $this->creatingNewClient = false;
    }

    public function pickMachine(int $machineId): void
    {
        $this->selectedMachineId = $machineId;
        $this->creatingNewMachine = false;
    }

    public function startNewMachine(): void
    {
        $this->creatingNewMachine = true;
        $this->selectedMachineId = null;
    }

    /**
     * A native <input multiple> replaces its whole selection every time the user picks files
     * again, so we accumulate each batch into a persistent queue instead of overwriting it.
     */
    public function updatedNewPhotos(): void
    {
        $this->validateOnly('newPhotos.*', [
            'newPhotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        foreach ($this->newPhotos as $photo) {
            $this->photoQueue[] = $photo;
        }

        $this->newPhotos = [];
    }

    public function removeQueuedPhoto(int $index): void
    {
        unset($this->photoQueue[$index]);
        $this->photoQueue = array_values($this->photoQueue);
    }

    /**
     * Same accumulate-on-select pattern as updatedNewPhotos(), but for photos of the reported
     * issue itself (e.g. physical damage) rather than the machine's identification photos.
     */
    public function updatedNewIssuePhotos(): void
    {
        $this->validateOnly('newIssuePhotos.*', [
            'newIssuePhotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        foreach ($this->newIssuePhotos as $photo) {
            $this->issuePhotoQueue[] = $photo;
        }

        $this->newIssuePhotos = [];
    }

    public function removeQueuedIssuePhoto(int $index): void
    {
        unset($this->issuePhotoQueue[$index]);
        $this->issuePhotoQueue = array_values($this->issuePhotoQueue);
    }

    protected function rules(): array
    {
        $rules = [
            'reported_issue' => ['nullable', 'string', 'max:2000'],
            'cc_email' => ['nullable', 'email', 'max:150'],
            'clientSignatureName' => ['required_with:clientSignatureData', 'nullable', 'string', 'max:150'],
            'issuePhotoQueue' => ['array', 'max:10'],
            'issuePhotoQueue.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];

        if (! $this->selectedClientId) {
            $rules = array_merge($rules, [
                'nc_first_name' => ['required', 'string', 'max:100'],
                'nc_last_name' => ['required', 'string', 'max:100'],
                'nc_type' => ['required', 'in:pro,particulier'],
                'nc_company_name' => ['nullable', 'required_if:nc_type,pro', 'string', 'max:150'],
                'nc_email' => ['nullable', 'email', 'max:150'],
                'nc_phone' => ['nullable', 'string', 'max:30'],
                'nc_address_line1' => ['nullable', 'string', 'max:150'],
                'nc_address_line2' => ['nullable', 'string', 'max:150'],
                'nc_postal_code' => ['nullable', 'string', 'max:20'],
                'nc_city' => ['nullable', 'string', 'max:100'],
            ]);
        }

        if ($this->creatingNewMachine) {
            $rules = array_merge($rules, [
                'm_brand' => ['required', 'string', 'max:100'],
                'm_model' => ['required', 'string', 'max:100'],
                'm_serial_number' => ['nullable', 'string', 'max:150'],
                'm_password' => ['nullable', 'string', 'max:255'],
                'm_notes' => ['nullable', 'string', 'max:2000'],
                'photoQueue' => ['array', 'max:10'],
                'photoQueue.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);
        } else {
            $rules['selectedMachineId'] = ['required', 'integer'];
        }

        return $rules;
    }

    public function save()
    {
        $this->validate();

        $intake = DB::transaction(function () {
            $client = $this->selectedClientId
                ? Client::findOrFail($this->selectedClientId)
                : Client::create([
                    'first_name' => $this->nc_first_name,
                    'last_name' => $this->nc_last_name,
                    'type' => $this->nc_type,
                    'company_name' => $this->nc_company_name ?: null,
                    'email' => $this->nc_email ?: null,
                    'phone' => $this->nc_phone ?: null,
                    'address_line1' => $this->nc_address_line1 ?: null,
                    'address_line2' => $this->nc_address_line2 ?: null,
                    'postal_code' => $this->nc_postal_code ?: null,
                    'city' => $this->nc_city ?: null,
                ]);

            if ($this->creatingNewMachine) {
                $machine = Machine::create([
                    'client_id' => $client->id,
                    'brand' => $this->m_brand,
                    'model' => $this->m_model,
                    'serial_number' => $this->m_serial_number ?: null,
                    'password' => $this->m_password ?: null,
                    'notes' => $this->m_notes ?: null,
                ]);

                foreach ($this->photoQueue as $photo) {
                    $path = $photo->store('machines/'.$machine->id, 'local');

                    MachinePhoto::create([
                        'machine_id' => $machine->id,
                        'disk' => 'local',
                        'path' => $path,
                        'original_name' => $photo->getClientOriginalName(),
                        'mime_type' => $photo->getMimeType(),
                        'size' => $photo->getSize(),
                    ]);
                }
            } else {
                $machine = Machine::where('client_id', $client->id)->findOrFail($this->selectedMachineId);
            }

            $status = Status::default() ?? Status::query()->orderBy('sort_order')->firstOrFail();

            $intake = Intake::create([
                'reference' => Intake::generateReference(),
                'client_id' => $client->id,
                'machine_id' => $machine->id,
                'status_id' => $status->id,
                'created_by' => Auth::id(),
                'reported_issue' => $this->reported_issue ?: null,
            ]);

            IntakeStatusHistory::create([
                'intake_id' => $intake->id,
                'status_id' => $status->id,
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);

            foreach ($this->issuePhotoQueue as $photo) {
                $path = $photo->store('intakes/'.$intake->id, 'local');

                IntakePhoto::create([
                    'intake_id' => $intake->id,
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => $photo->getClientOriginalName(),
                    'mime_type' => $photo->getMimeType(),
                    'size' => $photo->getSize(),
                ]);
            }

            $signatureUpdates = [];

            if ($this->clientSignatureData) {
                $path = Intake::storeSignatureImage($this->clientSignatureData, $intake->id, 'client');

                if ($path) {
                    $signatureUpdates['client_signature_path'] = $path;
                    $signatureUpdates['client_signature_name'] = $this->clientSignatureName;
                    $signatureUpdates['client_signed_at'] = now();
                }
            }

            if ($this->staffSignatureData) {
                $path = Intake::storeSignatureImage($this->staffSignatureData, $intake->id, 'staff');

                if ($path) {
                    $signatureUpdates['staff_signature_path'] = $path;
                    $signatureUpdates['staff_signed_by'] = Auth::id();
                    $signatureUpdates['staff_signed_at'] = now();
                }
            }

            if ($signatureUpdates) {
                $intake->update($signatureUpdates);
            }

            return $intake;
        });

        $this->sendCreationEmail($intake);

        session()->flash('status', "Prise en charge {$intake->reference} créée.");

        return $this->redirect(route('intakes.show', $intake), navigate: true);
    }

    protected function sendCreationEmail(Intake $intake): void
    {
        $intake->loadMissing(['client', 'machine', 'status']);

        if (! $intake->client->email) {
            return;
        }

        $cc = array_values(array_filter([$this->cc_email ?: null]));
        $bcc = [Auth::user()->email];

        $templateId = Setting::get(Setting::INTAKE_CREATED_TEMPLATE_ID);
        $template = $templateId ? EmailTemplate::find($templateId) : null;

        if ($template) {
            [$subject, $body] = $template->render([
                'reference' => $intake->reference,
                'client' => $intake->client->full_name,
                'machine' => "{$intake->machine->brand} {$intake->machine->model}",
                'statut' => $intake->status->label,
                'panne' => $intake->reported_issue ?: 'Non précisé.',
            ]);
            $mailable = new ClientNotificationMail($subject, $body, $template->attach_pdf ? $intake : null, $template->email_title);
            $templateKey = $template->key;
        } else {
            $subject = "Prise en charge {$intake->reference} enregistrée";
            $mailable = new IntakeCreated($intake);
            $templateKey = 'intake_created_default';
        }

        $notification = ClientNotification::create([
            'intake_id' => $intake->id,
            'template_key' => $templateKey,
            'subject' => $subject,
            'recipient_email' => $intake->client->email,
            'cc' => $cc,
            'bcc' => $bcc,
            'sent_by' => Auth::id(),
            'status' => 'queued',
        ]);

        $mailable->notificationId = $notification->id;

        try {
            Mail::to($intake->client->email)->cc($cc)->bcc($bcc)->send($mailable);
        } catch (\Throwable $e) {
            $notification->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.intakes.create', [
            'intakeTermsText' => Setting::get(Setting::INTAKE_TERMS_TEXT, ''),
        ]);
    }
}
