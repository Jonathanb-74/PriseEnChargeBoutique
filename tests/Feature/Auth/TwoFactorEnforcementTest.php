<?php

use App\Models\User;

test('local users without 2FA are redirected to setup when visiting a protected page', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('two-factor.setup'));
});

test('local users with 2FA enabled can access protected pages normally', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
});

test('azure users without 2FA are not forced through setup', function () {
    $user = User::factory()->withoutTwoFactor()->create(['azure_id' => 'fake-azure-id']);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
});

test('the two-factor setup page itself is reachable without a redirect loop', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $response = $this->actingAs($user)->get(route('two-factor.setup'));

    $response->assertOk();
});
