<?php

namespace Tests\Feature\Auth;

test('public registration is disabled', function () {
    $this->get('/register')->assertNotFound();
});
