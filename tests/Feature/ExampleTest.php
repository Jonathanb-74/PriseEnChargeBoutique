<?php

use App\Models\User;

it('redirects guests to the login page', function () {
    $this->get('/')->assertRedirect('/login');
});

it('redirects authenticated users to the dashboard', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/')->assertRedirect('/dashboard');
});
