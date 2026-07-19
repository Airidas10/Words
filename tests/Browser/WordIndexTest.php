<?php

it('shows the home page branding', function () {
    $page = visit('/');

    $page->assertPathIs('/')
        ->assertSee('Parole')
        ->assertSee("Ho Bisogno Di Imparare L'Italiano!")
        ->assertSee('Login')
        ->assertDontSee('Logout')
        ->assertNoJavascriptErrors();
});

it('shows an empty state when there are no words', function () {
    $page = visit('/');

    $page->assertSee('Nothing to show here')
        ->assertDontSee('Hide Translation')
        ->assertDontSee('Show Translation')
        ->assertDontSee('Create New');
});

it('lists words on the home page', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $page = visit('/');

    $page->assertSee('Ciao')
        ->assertSee('Hello')
        ->assertSee('Greeting')
        ->assertSee('Hide Translation')
        ->assertDontSee('Nothing to show here');
});

it('can toggle translations on the home page', function () {
    createWordWithTranslationAndTag('Grazie', 'Thank you', 'Polite');

    $page = visit('/');

    $page->assertSee('Thank you')
        ->assertSee('Polite')
        ->assertSee('Hide Translation')
        ->click('Hide Translation')
        ->assertSee('*****')
        ->assertDontSee('Thank you')
        ->assertSee('Show Translation')
        ->click('Show Translation')
        ->assertSee('Thank you')
        ->assertSee('Hide Translation');
});
