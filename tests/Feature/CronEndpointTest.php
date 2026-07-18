<?php

test('the cron endpoint runs the scheduler when the secret matches', function () {
    config(['services.cron_secret' => 'correct-secret']);

    $this->get('/cron/correct-secret')
        ->assertOk()
        ->assertSee('OK');
});

test('the cron endpoint 404s on a wrong secret', function () {
    config(['services.cron_secret' => 'correct-secret']);

    $this->get('/cron/wrong-secret')->assertNotFound();
});

test('the cron endpoint 404s when no secret is configured, even with a guessed token', function () {
    config(['services.cron_secret' => null]);

    $this->get('/cron/anything')->assertNotFound();
});
