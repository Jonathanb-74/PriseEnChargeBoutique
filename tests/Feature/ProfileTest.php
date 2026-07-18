<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/profile');

    $response
        ->assertOk()
        ->assertSeeVolt('profile.update-password-form')
        ->assertSeeVolt('profile.update-signature-form');
});

test('the profile page shows the user\'s name and email as read-only, not an edit form', function () {
    $user = User::factory()->create(['name' => 'Jean Dupont', 'email' => 'jean@example.com']);

    $this->actingAs($user);

    $response = $this->get('/profile');

    $response
        ->assertOk()
        ->assertSee('Jean Dupont')
        ->assertSee('jean@example.com')
        ->assertDontSeeVolt('profile.update-profile-information-form');
});
