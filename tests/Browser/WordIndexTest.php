<?php

use App\Models\User;

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

it('shows overall word stats on cards for a logged-in user with history', function () {
    $user = User::factory()->create([
        'username' => 'statsuser',
        'password' => 'password',
    ]);
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    createFinishedTestWithQuestions($user, [
        '1' => [
            'id' => $word->id,
            'type' => 'w',
            'question' => 'Ciao',
            'answer' => 'Hello',
            'correct' => true,
            'correctAnswer' => 'hello',
            'help' => '',
        ],
        '2' => [
            'id' => $word->id,
            'type' => 't',
            'question' => 'Hello',
            'answer' => 'wrong',
            'correct' => false,
            'correctAnswer' => 'ciao',
            'help' => '',
        ],
    ], score: 1);

    $page = loginThroughBrowser($user);

    $page->assertSee('Ciao')
        ->assertSeeIn('@word-stats-'.$word->id, '50%')
        ->click('@word-stats-'.$word->id)
        ->assertSeeIn('@word-stats-popover-'.$word->id, 'Your stats: 50% (1/2)')
        ->click('Close')
        ->assertDontSee('Your stats: 50% (1/2)')
        ->assertNoJavascriptErrors();
});

it('shows a dash and not-tested popover for a logged-in user with no history', function () {
    $user = User::factory()->create([
        'username' => 'statsempty',
        'password' => 'password',
    ]);
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $page = loginThroughBrowser($user);

    $page->assertSee('Ciao')
        ->assertSeeIn('@word-stats-'.$word->id, '—')
        ->click('@word-stats-'.$word->id)
        ->assertSeeIn('@word-stats-popover-'.$word->id, 'Not tested yet')
        ->click('Close')
        ->assertDontSee('Not tested yet')
        ->assertNoJavascriptErrors();
});

it('does not show word card stats controls to guests', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $page = visit('/');

    $page->assertSee('Ciao')
        ->assertMissing('@word-stats-'.$word->id)
        ->assertNoJavascriptErrors();
});
