<?php

use App\Enums\UserRole;
use App\Livewire\Users\Index as UsersIndex;
use App\Models\User;
use Livewire\Livewire;

test('technician cannot access the users page', function () {
    $technician = User::factory()->create(['role' => UserRole::Technicien]);

    $this->actingAs($technician);

    $this->get('/users')->assertForbidden();
});

test('admin can view users and change a role', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $technician = User::factory()->create(['role' => UserRole::Technicien]);

    $this->actingAs($admin);

    Livewire::test(UsersIndex::class)
        ->call('updateRole', $technician, UserRole::Admin->value)
        ->assertHasNoErrors();

    expect($technician->fresh()->role)->toBe(UserRole::Admin);
});

test('admin cannot change their own role from the users page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin);

    expect($admin->can('updateRole', $admin))->toBeFalse();
});
