<?php

use App\Enums\UserRole;
use App\Livewire\Intakes\Index as IntakesIndex;
use App\Livewire\Intakes\Show as IntakeShow;
use App\Livewire\Users\Index as UsersIndex;
use App\Models\Client;
use App\Models\Intake;
use App\Models\Machine;
use App\Models\Status;
use App\Models\User;
use Database\Seeders\StatusSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(StatusSeeder::class);
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
});

test('admin can toggle whether a user can be assigned to intakes', function () {
    $technician = User::factory()->create(['role' => UserRole::Technicien]);

    $this->actingAs($this->admin);

    Livewire::test(UsersIndex::class)
        ->call('toggleAssignable', $technician)
        ->assertHasNoErrors();

    expect($technician->fresh()->is_assignable)->toBeFalse();
});

test('a non-assignable user does not appear in the intakes technician filter', function () {
    $visible = User::factory()->create(['role' => UserRole::Technicien, 'is_assignable' => true]);
    $hidden = User::factory()->create(['role' => UserRole::Technicien, 'is_assignable' => false]);

    $this->actingAs($this->admin);

    Livewire::test(IntakesIndex::class)
        ->assertSee($visible->name)
        ->assertDontSee($hidden->name);
});

test('a non-assignable user cannot be newly assigned to an intake', function () {
    $client = Client::factory()->create();
    $machine = Machine::factory()->create(['client_id' => $client->id]);
    $intake = Intake::create([
        'reference' => Intake::generateReference(),
        'client_id' => $client->id,
        'machine_id' => $machine->id,
        'status_id' => Status::default()->id,
        'created_by' => $this->admin->id,
    ]);

    $hidden = User::factory()->create(['role' => UserRole::Technicien, 'is_assignable' => false]);

    $this->actingAs($this->admin);

    Livewire::test(IntakeShow::class, ['intake' => $intake])
        ->set('technicianId', $hidden->id)
        ->call('assignTechnician')
        ->assertHasErrors('technicianId');

    expect($intake->fresh()->technician_id)->toBeNull();
});

test('an intake keeps its already-assigned technician visible even after being made non-assignable', function () {
    $client = Client::factory()->create();
    $machine = Machine::factory()->create(['client_id' => $client->id]);
    $technician = User::factory()->create(['role' => UserRole::Technicien]);
    $intake = Intake::create([
        'reference' => Intake::generateReference(),
        'client_id' => $client->id,
        'machine_id' => $machine->id,
        'status_id' => Status::default()->id,
        'created_by' => $this->admin->id,
        'technician_id' => $technician->id,
    ]);

    $technician->update(['is_assignable' => false]);

    $this->actingAs($this->admin);

    Livewire::test(IntakeShow::class, ['intake' => $intake])
        ->assertSee($technician->name);
});
