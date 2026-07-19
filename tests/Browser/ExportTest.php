<?php

it('does not show export to guests', function () {
    $page = visit('/');

    $page->assertDontSee('Export')
        ->assertSee('Login');
});

it('redirects guests away from export', function () {
    $page = visit('/export');

    $page->assertPathIs('/login')
        ->assertSee('Login');
});

it('shows export to an authenticated user', function () {
    $page = loginThroughBrowser()
        ->assertSee('Export')
        ->assertSee('Logout')
        ->assertDontSee('Login');
});
