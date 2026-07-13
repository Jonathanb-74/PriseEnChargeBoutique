<?php

use App\Enums\UserRole;
use App\Livewire\Intakes\Show;
use App\Mail\ClientNotificationMail;
use App\Models\Client;
use App\Models\EmailTemplate;
use App\Models\Intake;
use App\Models\Machine;
use App\Models\Status;
use App\Models\User;
use Database\Seeders\StatusSeeder;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(StatusSeeder::class);
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);

    $client = Client::factory()->create(['email' => 'client@example.com']);
    $machine = Machine::factory()->create(['client_id' => $client->id, 'password' => 'super-secret']);

    $this->intake = Intake::create([
        'reference' => Intake::generateReference(),
        'client_id' => $client->id,
        'machine_id' => $machine->id,
        'status_id' => Status::default()->id,
        'created_by' => $this->admin->id,
    ]);
});

test('the internal pdf route succeeds and the client pdf route succeeds', function () {
    $this->actingAs($this->admin);

    $this->get(route('intakes.pdf', $this->intake))->assertOk();
    $this->get(route('intakes.pdf.client', $this->intake))->assertOk();
});

test('the pdf view only renders the machine password when includePassword is true', function () {
    $this->intake->loadMissing(['client', 'machine', 'status', 'technician', 'createdBy', 'staffSignedBy']);

    $withPassword = view('pdf.intake', [
        'intake' => $this->intake,
        'includePassword' => true,
        'clientSignaturePath' => null,
        'staffSignaturePath' => null,
        'issuePhotoPaths' => [],
        'intakeTermsText' => '',
        'pdfLogoPath' => null,
        'accentColor' => '#4f46e5',
    ])->render();

    $withoutPassword = view('pdf.intake', [
        'intake' => $this->intake,
        'includePassword' => false,
        'clientSignaturePath' => null,
        'staffSignaturePath' => null,
        'issuePhotoPaths' => [],
        'intakeTermsText' => '',
        'pdfLogoPath' => null,
        'accentColor' => '#4f46e5',
    ])->render();

    expect($withPassword)->toContain('super-secret')
        ->and($withoutPassword)->not->toContain('super-secret');
});

test('a template with attach_pdf sends the client-safe pdf as an attachment', function () {
    Mail::fake();

    $this->actingAs($this->admin);

    $template = EmailTemplate::create([
        'key' => 'with_pdf',
        'name' => 'Avec PDF',
        'subject' => 'Sujet {{reference}}',
        'body' => 'Corps du message',
        'is_active' => true,
        'attach_pdf' => true,
    ]);

    Livewire::test(Show::class, ['intake' => $this->intake])
        ->set('selectedTemplateId', $template->id)
        ->set('notif_subject', 'Sujet test')
        ->set('notif_body', 'Corps test')
        ->call('sendNotification')
        ->assertHasNoErrors();

    Mail::assertSent(ClientNotificationMail::class, function ($mail) {
        return $mail->pdfIntake !== null && count($mail->attachments()) === 1;
    });
});

test('a template without attach_pdf does not attach anything', function () {
    Mail::fake();

    $this->actingAs($this->admin);

    Livewire::test(Show::class, ['intake' => $this->intake])
        ->set('notif_subject', 'Sujet test')
        ->set('notif_body', 'Corps test')
        ->call('sendNotification')
        ->assertHasNoErrors();

    Mail::assertSent(ClientNotificationMail::class, function ($mail) {
        return $mail->pdfIntake === null && count($mail->attachments()) === 0;
    });
});
