<?php

use App\Enums\UserRole;
use App\Livewire\Intakes\Create;
use App\Mail\ClientNotificationMail;
use App\Mail\IntakeCreated;
use App\Models\ClientNotification;
use App\Models\EmailTemplate;
use App\Models\Intake;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\StatusSeeder;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(StatusSeeder::class);
    $this->user = User::factory()->create(['role' => UserRole::Admin]);
});

test('creating an intake with a new client and new machine sends a confirmation email', function () {
    Mail::fake();

    $this->actingAs($this->user);

    Livewire::test(Create::class)
        ->set('nc_first_name', 'Jean')
        ->set('nc_last_name', 'Dupont')
        ->set('nc_type', 'particulier')
        ->set('nc_email', 'jean.dupont@example.com')
        ->set('nc_phone', '0600000000')
        ->set('m_brand', 'Dell')
        ->set('m_model', 'Latitude 5400')
        ->set('m_serial_number', 'SN-12345')
        ->set('m_password', 'secret-machine-pass')
        ->set('reported_issue', "Ne s'allume plus")
        ->call('save')
        ->assertHasNoErrors();

    $intake = Intake::firstOrFail();

    expect($intake->client->email)->toBe('jean.dupont@example.com')
        ->and($intake->machine->brand)->toBe('Dell')
        ->and($intake->machine->password)->toBe('secret-machine-pass')
        ->and($intake->reference)->toStartWith('PEC-')
        ->and($intake->statusHistories()->count())->toBe(1);

    Mail::assertSent(IntakeCreated::class, function ($mail) use ($intake) {
        return $mail->intake->is($intake);
    });

    expect(ClientNotification::where('intake_id', $intake->id)->where('status', 'sent')->exists())->toBeTrue();
});

test('a configured template is used instead of the built-in one when creating an intake', function () {
    Mail::fake();

    $template = EmailTemplate::create([
        'key' => 'custom_creation',
        'name' => 'Modèle personnalisé',
        'subject' => 'Bonjour {{client}}, prise en charge {{reference}}',
        'body' => 'Votre machine {{machine}} est enregistrée, statut : {{statut}}.',
        'is_active' => true,
    ]);

    Setting::set(Setting::INTAKE_CREATED_TEMPLATE_ID, (string) $template->id);

    $this->actingAs($this->user);

    Livewire::test(Create::class)
        ->set('nc_first_name', 'Jean')
        ->set('nc_last_name', 'Dupont')
        ->set('nc_type', 'particulier')
        ->set('nc_email', 'jean.dupont@example.com')
        ->set('m_brand', 'Dell')
        ->set('m_model', 'Latitude 5400')
        ->call('save')
        ->assertHasNoErrors();

    $intake = Intake::firstOrFail();

    Mail::assertNotSent(IntakeCreated::class);
    Mail::assertSent(ClientNotificationMail::class, function ($mail) {
        return str_contains($mail->subjectLine, 'Jean Dupont')
            && str_contains($mail->body, 'Dell Latitude 5400');
    });

    $notification = ClientNotification::where('intake_id', $intake->id)->firstOrFail();
    expect($notification->template_key)->toBe('custom_creation');
});
