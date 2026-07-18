<?php

use App\Livewire\TwoFactor\Challenge;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

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

test('the two-factor challenge is rate limited after 5 failed attempts', function () {
    $user = User::factory()->create(['two_factor_secret' => 'JBSWY3DPEHPK3PXP']);

    RateLimiter::clear('2fa:'.$user->id.'|127.0.0.1');

    session(['2fa.user_id' => $user->id]);

    $component = Livewire::test(Challenge::class);

    foreach (range(1, 5) as $i) {
        $component->set('code', '000000')->call('verify')->assertHasErrors('code');
    }

    $component->set('code', '000000')->call('verify');

    expect($component->errors()->first('code'))->toContain('Trop de tentatives');

    expect(auth()->check())->toBeFalse();
});
