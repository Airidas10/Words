<?php

use App\Models\User;

it('shows a my struggles link on the homepage for logged-in users and opens the page', function () {
    $user = User::factory()->create([
        'username' => 'struggleshome',
        'password' => 'password',
    ]);
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    attachStruggleWord($user, $word);

    $page = loginThroughBrowser($user);

    $page->assertSee('My Struggles')
        ->click('My Struggles')
        ->assertPathIs('/my-struggles')
        ->assertSee('Ciao')
        ->assertNoJavascriptErrors();
});

it('does not show a my struggles link on the homepage for guests', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    visit('/')
        ->assertDontSee('My Struggles')
        ->assertNoJavascriptErrors();
});

it('adds a word to struggles from a word card', function () {
    $user = User::factory()->create([
        'username' => 'strugglecardadd',
        'password' => 'password',
    ]);
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $page = loginThroughBrowser($user);

    $page->assertSee('Ciao')
        ->assertPresent('@struggle-toggle-'.$word->id)
        ->assertDataAttribute('@struggle-toggle-'.$word->id, 'in-struggles', 'false')
        ->click('@struggle-toggle-'.$word->id)
        ->assertDataAttribute('@struggle-toggle-'.$word->id, 'in-struggles', 'true')
        ->assertNoJavascriptErrors();

    expect($user->fresh()->struggleWords->pluck('id')->all())->toContain($word->id);
});

it('removes a word from struggles on the word show page', function () {
    $user = User::factory()->create([
        'username' => 'strugglewordremove',
        'password' => 'password',
    ]);
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    attachStruggleWord($user, $word);

    $page = loginThroughBrowser($user);

    $page->click('Ciao')
        ->assertPathIs('/words/'.$word->id)
        ->assertPresent('@struggle-toggle-'.$word->id)
        ->assertDataAttribute('@struggle-toggle-'.$word->id, 'in-struggles', 'true')
        ->click('@struggle-toggle-'.$word->id)
        ->assertDataAttribute('@struggle-toggle-'.$word->id, 'in-struggles', 'false')
        ->assertNoJavascriptErrors();

    expect($user->fresh()->struggleWords->pluck('id')->all())->not->toContain($word->id);
});

it('removes a word from the my struggles page', function () {
    $user = User::factory()->create([
        'username' => 'strugglepageremove',
        'password' => 'password',
    ]);
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    attachStruggleWord($user, $word);

    $page = loginThroughBrowser($user);

    $page->click('My Struggles')
        ->assertPathIs('/my-struggles')
        ->assertSee('Ciao')
        ->click('@struggle-toggle-'.$word->id)
        ->assertDontSee('Ciao')
        ->assertNoJavascriptErrors();

    expect($user->fresh()->struggleWords->pluck('id')->all())->not->toContain($word->id);
});

it('shows my struggles in the random pool dropdown for authenticated users', function () {
    $user = User::factory()->create([
        'username' => 'strugglerandom',
        'password' => 'password',
    ]);
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    attachStruggleWord($user, $word);

    $page = loginThroughBrowser($user);

    $page->click('Random')
        ->assertPathIs('/random')
        ->assertPresent('@random-pool-select')
        ->assertSourceHas('My Struggles')
        ->assertSourceHas('Full Random')
        ->assertNoJavascriptErrors();
});

it('does not show my struggles in the random pool dropdown for guests', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    visit('/random')
        ->assertPathIs('/random')
        ->assertPresent('@random-pool-select')
        ->assertSourceHas('Full Random')
        ->assertSourceMissing('My Struggles')
        ->assertNoJavascriptErrors();
});
