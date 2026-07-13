<?php

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Intake;
use App\Models\Machine;
use App\Models\Status;
use App\Models\User;
use Database\Seeders\StatusSeeder;

beforeEach(function () {
    $this->seed(StatusSeeder::class);
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
});

test('guest is redirected away from protected pages', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('admin can reach the core pages', function () {
    $this->actingAs($this->admin);

    $this->get('/dashboard')->assertOk();
    $this->get('/clients')->assertOk();
    $this->get('/clients/create')->assertOk();
    $this->get('/intakes')->assertOk();
    $this->get('/intakes/create')->assertOk();
    $this->get('/statuses')->assertOk();
    $this->get('/email-templates')->assertOk();
    $this->get('/profile')->assertOk();
    $this->get('/settings/two-factor')->assertOk();
});

test('client, machine and intake show pages render, including the pdf', function () {
    $this->actingAs($this->admin);

    $client = Client::factory()->create();
    $machine = Machine::factory()->create(['client_id' => $client->id]);
    $status = Status::default();

    $intake = Intake::create([
        'reference' => Intake::generateReference(),
        'client_id' => $client->id,
        'machine_id' => $machine->id,
        'status_id' => $status->id,
        'created_by' => $this->admin->id,
    ]);

    $this->get("/clients/{$client->id}")->assertOk();
    $this->get("/machines/{$machine->id}/edit")->assertOk();
    $this->get("/intakes/{$intake->id}")->assertOk();
    $this->get("/intakes/{$intake->id}/pdf")->assertOk();
});

test('technician cannot delete a client', function () {
    $technician = User::factory()->create(['role' => UserRole::Technicien]);
    $client = Client::factory()->create();

    $this->actingAs($technician);

    expect($technician->can('delete', $client))->toBeFalse();
});
