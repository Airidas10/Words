<?php

use App\Models\User;

it('logs in with valid credentials through the login form', function () {
    User::factory()->create([
        'username' => 'reallogin',
        'password' => 'password',
    ]);

    visit('/login')
        ->assertSee('Login')
        ->fill('#username', 'reallogin')
        ->fill('#password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/')
        ->assertSee('Logout')
        ->assertDontSee('Login')
        ->assertNoJavascriptErrors();
});
