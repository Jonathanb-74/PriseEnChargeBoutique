<?php

use App\Enums\UserRole;
use App\Livewire\Intakes\Show;
use App\Livewire\Machines\Form as MachineForm;
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

test('selecting photos multiple times on the machine form accumulates them instead of replacing them', function () {
    $client = Client::factory()->create();

    $this->actingAs($this->admin);

    $component = Livewire::test(MachineForm::class, ['client' => $client])
        ->set('brand', 'Dell')
        ->set('model', 'Latitude')
        ->set('newPhotos', [UploadedFile::fake()->image('one.jpg')])
        ->set('newPhotos', [UploadedFile::fake()->image('two.jpg')])
        ->set('newPhotos', [UploadedFile::fake()->image('three.jpg')]);

    expect($component->get('photoQueue'))->toHaveCount(3);

    $component->call('save')->assertHasNoErrors();

    $machine = Machine::firstOrFail();
    expect($machine->photos()->count())->toBe(3);
});

test('a queued photo can be removed before saving', function () {
    $client = Client::factory()->create();

    $this->actingAs($this->admin);

    $component = Livewire::test(MachineForm::class, ['client' => $client])
        ->set('brand', 'Dell')
        ->set('model', 'Latitude')
        ->set('newPhotos', [UploadedFile::fake()->image('one.jpg')])
        ->set('newPhotos', [UploadedFile::fake()->image('two.jpg')]);

    expect($component->get('photoQueue'))->toHaveCount(2);

    $component->call('removeQueuedPhoto', 0)->call('save')->assertHasNoErrors();

    expect(Machine::firstOrFail()->photos()->count())->toBe(1);
});

test('photos can be added to and removed from a machine directly from the intake page', function () {
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
        ->set('newPhotos', [UploadedFile::fake()->image('one.jpg')])
        ->assertHasNoErrors();

    expect($machine->photos()->count())->toBe(1);

    $photo = $machine->photos()->firstOrFail();

    Livewire::test(Show::class, ['intake' => $intake])
        ->call('deletePhoto', $photo)
        ->assertHasNoErrors();

    expect($machine->photos()->count())->toBe(0);
});
