<?php

use App\Models\User;

it('does not show my tests navigation to guests', function () {
    $page = visit('/');

    $page->assertDontSee('My Tests')
        ->assertSee('Login');
});

it('redirects guests away from my tests', function () {
    $page = visit('/my-tests');

    $page->assertPathIs('/login')
        ->assertSee('Login');
});

it('shows an empty my tests page for an authenticated user', function () {
    $page = loginThroughBrowser()
        ->assertSee('My Tests')
        ->click('My Tests')
        ->assertPathIs('/my-tests')
        ->assertSee('My Tests')
        ->assertSee('Total Tests')
        ->assertSee('Average Score')
        ->assertSeeIn('@total-tests-value', '0')
        ->assertSeeIn('@average-score-value', '-')
        ->assertDontSee('See more');
});

it('lists finished tests with scores for an authenticated user', function () {
    $user = User::factory()->create();

    $fullScore = createFinishedTest($user, score: 3, numberOfQuestions: 3);
    $lowScore = createFinishedTest($user, score: 1, numberOfQuestions: 3);
    createUnfinishedTest($user, numberOfQuestions: 3);

    $page = loginThroughBrowser($user)
        ->click('My Tests')
        ->assertPathIs('/my-tests')
        ->assertSee('My Tests')
        ->assertSee('Total Tests')
        ->assertSee('Average Score')
        ->assertSeeIn('@total-tests-value', '2')
        ->assertSeeIn('@average-score-value', '2.00')
        ->assertSeeIn('@test-score-'.$fullScore->id, '3/3')
        ->assertSeeIn('@test-score-'.$lowScore->id, '1/3')
        ->assertSee('See more');
});

it('opens a finished test from see more', function () {
    $user = User::factory()->create();
    $test = createFinishedTest($user, score: 2, numberOfQuestions: 3);

    $page = loginThroughBrowser($user)
        ->click('My Tests')
        ->assertSeeIn('@test-score-'.$test->id, '2/3')
        ->click('See more')
        ->assertPathIs('/runs/'.$test->id)
        ->assertSee('Your score is: 2 / 3')
        ->assertSee('Ciao')
        ->assertSee('Hello')
        ->assertSee('hello');
});
