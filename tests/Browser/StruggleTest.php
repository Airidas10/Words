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
        ->click('@random-pool-select')
        ->assertPresent('@random-pool-panel')
        ->assertSee('Full Random')
        ->assertSee('My Struggles')
        ->assertNoJavascriptErrors();
});

it('selects my struggles pool from the random picker', function () {
    $user = User::factory()->create([
        'username' => 'strugglepool',
        'password' => 'password',
    ]);
    $struggle = createWordWithTranslationAndTag('StruggleOnly', 'Only mine', 'Greeting');
    createWordWithTranslationAndTag('NotStruggle', 'Elsewhere', 'Polite');
    attachStruggleWord($user, $struggle);

    $page = loginThroughBrowser($user);

    $page->click('Random')
        ->assertPathIs('/random')
        ->click('@random-pool-select')
        ->click('@random-pool-option-struggles')
        ->assertPathIs('/random')
        ->assertQueryStringHas('pool', 'struggles')
        ->assertSee('StruggleOnly')
        ->assertDontSee('NotStruggle')
        ->assertNoJavascriptErrors();
});

it('does not show my struggles in the random pool dropdown for guests', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    visit('/random')
        ->assertPathIs('/random')
        ->assertPresent('@random-pool-select')
        ->click('@random-pool-select')
        ->assertPresent('@random-pool-panel')
        ->assertSee('Full Random')
        ->assertDontSee('My Struggles')
        ->assertNoJavascriptErrors();
});

it('loads struggle proposals only after the user triggers suggestions', function () {
    $user = User::factory()->create([
        'username' => 'strugglepropose',
        'password' => 'password',
    ]);
    $listed = createWordWithTranslationAndTag('Listed', 'On list', 'Stats');
    $worst = createWordWithTranslationAndTag('WorstPropose', 'Bad', 'Stats');

    attachStruggleWord($user, $listed);
    recordWordAttempts($user, $worst, correct: 0, incorrect: 2);
    recordWordAttempts($user, $listed, correct: 1, incorrect: 1);

    $page = loginThroughBrowser($user);

    $page->click('My Struggles')
        ->assertPathIs('/my-struggles')
        ->assertSee('Listed')
        ->assertDontSee('WorstPropose')
        ->assertPresent('@struggle-proposals-trigger')
        ->assertMissing('@struggle-proposals')
        ->click('@struggle-proposals-trigger')
        ->assertPresent('@struggle-proposals')
        ->assertSeeIn('@struggle-proposals', 'WorstPropose')
        ->assertPresent('@struggle-propose-toggle-'.$worst->id)
        ->assertDataAttribute('@struggle-propose-toggle-'.$worst->id, 'in-struggles', 'false')
        ->assertEnabled('@struggle-propose-toggle-'.$worst->id)
        ->assertNoJavascriptErrors();
});

it('adds a proposed word to struggles and leaves the proposal control unaddable', function () {
    $user = User::factory()->create([
        'username' => 'struggleproposeadd',
        'password' => 'password',
    ]);
    $worst = createWordWithTranslationAndTag('WorstAdd', 'Bad', 'Stats');
    recordWordAttempts($user, $worst, correct: 0, incorrect: 2);

    $page = loginThroughBrowser($user);

    $page->click('My Struggles')
        ->assertPathIs('/my-struggles')
        ->click('@struggle-proposals-trigger')
        ->assertPresent('@struggle-propose-toggle-'.$worst->id)
        ->click('@struggle-propose-toggle-'.$worst->id)
        ->assertDataAttribute('@struggle-propose-toggle-'.$worst->id, 'in-struggles', 'true')
        ->assertDisabled('@struggle-propose-toggle-'.$worst->id)
        ->assertPresent('@struggle-toggle-'.$worst->id)
        ->assertNoJavascriptErrors();

    $this->assertDatabaseHas('user_word', [
        'user_id' => $user->id,
        'word_id' => $worst->id,
    ]);
});

it('shows already-listed proposed words as unaddable', function () {
    $user = User::factory()->create([
        'username' => 'struggleproposeunaddable',
        'password' => 'password',
    ]);
    $word = createWordWithTranslationAndTag('AlreadyListed', 'Bad', 'Stats');
    recordWordAttempts($user, $word, correct: 0, incorrect: 2);
    attachStruggleWord($user, $word);

    $page = loginThroughBrowser($user);

    $page->click('My Struggles')
        ->assertPathIs('/my-struggles')
        ->assertSee('AlreadyListed')
        ->click('@struggle-proposals-trigger')
        ->assertPresent('@struggle-propose-toggle-'.$word->id)
        ->assertDataAttribute('@struggle-propose-toggle-'.$word->id, 'in-struggles', 'true')
        ->assertDisabled('@struggle-propose-toggle-'.$word->id)
        ->assertNoJavascriptErrors();
});
