<?php

use App\Enums\UserRole;
use App\Livewire\Intakes\Show;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Models\Client;
use App\Models\Intake;
use App\Models\Machine;
use App\Models\Setting;
use App\Models\Status;
use App\Models\User;
use Database\Seeders\StatusSeeder;
use Livewire\Livewire;

const TINY_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

beforeEach(function () {
    $this->seed(StatusSeeder::class);
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
});

test('admin can update the intake terms text setting', function () {
    $this->actingAs($this->admin);

    Livewire::test(SettingsIndex::class)
        ->set('intakeTermsText', 'Tarif minimum : 30€')
        ->set('mailMailer', 'log')
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::get(Setting::INTAKE_TERMS_TEXT))->toBe('Tarif minimum : 30€');
});

test('client and staff signatures can be captured from the intake show page', function () {
    $this->actingAs($this->admin);

    $client = Client::factory()->create();
    $machine = Machine::factory()->create(['client_id' => $client->id]);
    $intake = Intake::create([
        'reference' => Intake::generateReference(),
        'client_id' => $client->id,
        'machine_id' => $machine->id,
        'status_id' => Status::default()->id,
        'created_by' => $this->admin->id,
    ]);

    $dataUrl = 'data:image/png;base64,'.TINY_PNG_BASE64;

    Livewire::test(Show::class, ['intake' => $intake])
        ->set('clientSignatureName', 'Jean Dupont')
        ->set('clientSignatureData', $dataUrl)
        ->call('saveClientSignature')
        ->assertHasNoErrors()
        ->set('staffSignatureData', $dataUrl)
        ->call('saveStaffSignature')
        ->assertHasNoErrors();

    $intake->refresh();

    expect($intake->isFullySigned())->toBeTrue()
        ->and($intake->client_signature_name)->toBe('Jean Dupont')
        ->and($intake->staff_signed_by)->toBe($this->admin->id);

    $this->get(route('intakes.signature', [$intake, 'client']))->assertOk();
    $this->get(route('intakes.signature', [$intake, 'staff']))->assertOk();
    $this->get(route('intakes.pdf', $intake))->assertOk();
});
