<?php

use App\Models\User;

it('logs in with valid credentials through the login form', function () {
    User::factory()->create([
        'username' => 'reallogin',
        'password' => 'password',
    ]);

    $page = visit('/login')
        ->assertSee('Login')
        ->fill('#username', 'reallogin')
        ->fill('#password', 'password');

    $page->click('@login-submit');

    waitForBrowser(fn () => $page
        ->assertPathIs('/')
        ->assertSee('Logout')
        ->assertDontSee('Login')
        ->assertNoJavascriptErrors());
});
