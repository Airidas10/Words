<?php

use App\Models\User;
use App\Models\Word;

it('opens a word show page from the home page for guests', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $word->update(['description' => 'A common greeting']);

    $page = visit('/')
        ->assertSee('Ciao')
        ->click('Ciao')
        ->assertPathIs('/words/'.$word->id)
        ->assertSee('Ciao')
        ->assertSee('Hello')
        ->assertSee('Greeting')
        ->assertSee('Tags:')
        ->assertSee('Description:')
        ->assertSee('A common greeting')
        ->assertSee('Hide Translation')
        ->assertDontSee('Next')
        ->assertDontSee('Not tested yet')
        ->assertDontSee('Your stats:')
        ->assertNoJavascriptErrors();
});

it('can toggle translations on the word show page', function () {
    createWordWithTranslationAndTag('Grazie', 'Thank you', 'Polite');

    $page = visit('/')
        ->click('Grazie')
        ->assertSee('Thank you')
        ->assertSee('Hide Translation')
        ->click('Hide Translation')
        ->assertSee('*****')
        ->assertDontSee('Thank you')
        ->assertSee('Show Translation')
        ->assertSee('Tags are hidden.')
        ->click('Show Translation')
        ->assertSee('Thank you')
        ->assertSee('Hide Translation')
        ->assertSee('Tags:');
});

it('shows an empty tags state on the word show page', function () {
    $word = Word::factory()->create(['word' => 'Solo']);
    $word->translations()->create(['translation' => 'Alone']);

    $page = visit('/words/'.$word->id)
        ->assertPathIs('/words/'.$word->id)
        ->assertSee('Solo')
        ->assertSee('Alone')
        ->assertSee('No tags available.')
        ->assertDontSee('Tags:');
});

it('shows translations heading for multiple translations', function () {
    $word = Word::factory()->create(['word' => 'Ciao']);
    $word->translations()->create(['translation' => 'Hello']);
    $word->translations()->create(['translation' => 'Hi']);

    $page = visit('/words/'.$word->id)
        ->assertSee('Translations:')
        ->assertSee('Hello')
        ->assertSee('Hi');
});

it('shows not tested yet on word show for a logged-in user without history', function () {
    $user = User::factory()->create([
        'username' => 'wordshowstats',
        'password' => 'password',
    ]);
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $page = loginThroughBrowser($user);

    $page->click('Ciao')
        ->assertPathIs('/words/'.$word->id)
        ->assertSeeIn('@word-stats-'.$word->id, 'Not tested yet')
        ->assertDontSee('Your stats:')
        ->assertNoJavascriptErrors();
});

it('shows detailed stats on word show for a logged-in user with history', function () {
    $user = User::factory()->create([
        'username' => 'wordshowhistory',
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

    $page->click('Ciao')
        ->assertPathIs('/words/'.$word->id)
        ->assertSeeIn('@word-stats-'.$word->id, 'Your stats: 50% (1/2)')
        ->assertDontSee('Not tested yet')
        ->assertNoJavascriptErrors();
});
