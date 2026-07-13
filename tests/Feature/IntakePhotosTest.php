<?php

use App\Enums\UserRole;
use App\Livewire\Intakes\Create;
use App\Livewire\Intakes\Show;
use App\Models\Client;
use App\Models\Intake;
use App\Models\Machine;
use App\Models\Status;
use App\Models\User;
use Database\Seeders\StatusSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
    $this->seed(StatusSeeder::class);
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
});

test('issue photos selected during intake creation accumulate and end up attached to the intake, not the machine', function () {
    $this->actingAs($this->admin);

    Livewire::test(Create::class)
        ->set('nc_first_name', 'Jean')
        ->set('nc_last_name', 'Dupont')
        ->set('nc_type', 'particulier')
        ->set('m_brand', 'Dell')
        ->set('m_model', 'Latitude')
        ->set('newIssuePhotos', [UploadedFile::fake()->image('damage-1.jpg')])
        ->set('newIssuePhotos', [UploadedFile::fake()->image('damage-2.jpg')])
        ->call('save')
        ->assertHasNoErrors();

    $intake = Intake::firstOrFail();

    expect($intake->photos()->count())->toBe(2)
        ->and($intake->machine->photos()->count())->toBe(0);
});

test('issue photos can be added to and removed from an already-created intake', function () {
    $client = Client::factory()->create();
    $machine = Machine::factory()->create(['client_id' => $client->id]);
    $intake = Intake::create([
        'reference' => Intake::generateReference(),
        'client_id' => $client->id,
        'machine_id' => $machine->id,
        'status_id' => Status::default()->id,
        'created_by' => $this->admin->id,
    ]);

    $this->actingAs($this->admin);

    Livewire::test(Show::class, ['intake' => $intake])
        ->set('newIssuePhotos', [UploadedFile::fake()->image('damage.jpg')])
        ->assertHasNoErrors();

    expect($intake->photos()->count())->toBe(1)
        ->and($machine->photos()->count())->toBe(0);

    $photo = $intake->photos()->firstOrFail();

    Livewire::test(Show::class, ['intake' => $intake])
        ->call('deleteIssuePhoto', $photo)
        ->assertHasNoErrors();

    expect($intake->photos()->count())->toBe(0);
});
