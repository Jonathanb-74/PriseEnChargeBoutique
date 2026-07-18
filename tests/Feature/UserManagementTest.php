<?php

use App\Enums\UserRole;
use App\Livewire\Users\Index as UsersIndex;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
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

test('admin can create a local user and a password-setup email is sent', function () {
    Notification::fake();

    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin);

    Livewire::test(UsersIndex::class)
        ->set('name', 'Jean Dupont')
        ->set('email', 'jean.dupont@example.com')
        ->set('role', UserRole::Technicien->value)
        ->call('save')
        ->assertHasNoErrors();

    $newUser = User::where('email', 'jean.dupont@example.com')->firstOrFail();

    expect($newUser->name)->toBe('Jean Dupont')
        ->and($newUser->password)->toBeNull()
        ->and($newUser->usesLocalAuth())->toBeTrue();

    Notification::assertSentTo($newUser, \Illuminate\Auth\Notifications\ResetPassword::class);
});

test('admin can edit another user\'s name and email', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $technician = User::factory()->create(['role' => UserRole::Technicien, 'name' => 'Old Name']);

    $this->actingAs($admin);

    Livewire::test(UsersIndex::class)
        ->call('edit', $technician)
        ->set('name', 'New Name')
        ->set('email', 'new-email@example.com')
        ->call('save')
        ->assertHasNoErrors();

    $technician->refresh();

    expect($technician->name)->toBe('New Name')
        ->and($technician->email)->toBe('new-email@example.com');
});

test('admin can edit their own name and email from the users page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'name' => 'Old Name']);

    $this->actingAs($admin);

    expect($admin->can('update', $admin))->toBeTrue();

    Livewire::test(UsersIndex::class)
        ->call('edit', $admin)
        ->set('name', 'New Name')
        ->set('email', 'new-admin-email@example.com')
        ->call('save')
        ->assertHasNoErrors();

    expect($admin->fresh()->name)->toBe('New Name');
});

test('admin cannot edit the name and email of an Azure account', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $azureUser = User::factory()->withoutTwoFactor()->create(['role' => UserRole::Technicien, 'azure_id' => 'fake-azure-id']);

    $this->actingAs($admin);

    expect($admin->can('update', $azureUser))->toBeFalse();

    Livewire::test(UsersIndex::class)
        ->call('edit', $azureUser)
        ->assertForbidden();
});

test('admin can reset a local user\'s password', function () {
    Notification::fake();

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $technician = User::factory()->create(['role' => UserRole::Technicien]);
    $oldPasswordHash = $technician->password;

    $this->actingAs($admin);

    Livewire::test(UsersIndex::class)->call('resetPassword', $technician);

    $technician->refresh();

    expect($technician->password)->toBeNull()
        ->and($technician->password)->not->toBe($oldPasswordHash);

    Notification::assertSentTo($technician, \Illuminate\Auth\Notifications\ResetPassword::class);
});

test('admin can reset a local user\'s 2FA', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $technician = User::factory()->create(['role' => UserRole::Technicien]);

    expect($technician->hasTwoFactorEnabled())->toBeTrue();

    $this->actingAs($admin);

    Livewire::test(UsersIndex::class)->call('resetTwoFactor', $technician);

    $technician->refresh();

    expect($technician->hasTwoFactorEnabled())->toBeFalse()
        ->and($technician->two_factor_secret)->toBeNull()
        ->and($technician->two_factor_recovery_codes)->toBeNull();
});

test('admin cannot reset password or 2FA for an Azure account', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $azureUser = User::factory()->withoutTwoFactor()->create(['role' => UserRole::Technicien, 'azure_id' => 'fake-azure-id']);

    $this->actingAs($admin);

    expect($admin->can('resetPassword', $azureUser))->toBeFalse()
        ->and($admin->can('resetTwoFactor', $azureUser))->toBeFalse();
});

test('admin cannot reset their own password or 2FA', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin);

    expect($admin->can('resetPassword', $admin))->toBeFalse()
        ->and($admin->can('resetTwoFactor', $admin))->toBeFalse();
});

test('technician cannot create, edit, or reset users', function () {
    $technician = User::factory()->create(['role' => UserRole::Technicien]);
    $otherUser = User::factory()->create(['role' => UserRole::Technicien]);

    $this->actingAs($technician);

    expect($technician->can('create', User::class))->toBeFalse()
        ->and($technician->can('update', $otherUser))->toBeFalse()
        ->and($technician->can('resetPassword', $otherUser))->toBeFalse()
        ->and($technician->can('resetTwoFactor', $otherUser))->toBeFalse();
});
