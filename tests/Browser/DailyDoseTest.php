<?php

use App\Models\Test;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('words.questions_per_test', 3);
});

it('redirects guests away from the daily dose page', function () {
    $page = visit('/daily-dose');

    $page->assertPathIs('/login')
        ->assertSee('Login');
});

it('lets an authenticated user start a daily dose', function () {
    createWordPool(10);

    $page = loginThroughBrowser()
        ->click('Daily Dose')
        ->assertPathIs('/daily-dose')
        ->assertSee('Submit')
        ->assertNoJavascriptErrors();

    expect(Test::count())->toBe(1);
    expect(Test::first()->score)->toBeNull();
    expect(Test::first()->number_of_questions)->toBe(3);
});

it('keeps the same unfinished test after leaving and returning', function () {
    createWordPool(10);

    $page = loginThroughBrowser()
        ->click('Daily Dose')
        ->assertSee('Submit');

    $test = Test::first();
    $questions = collect(json_decode($test->questions_and_answers, true))->pluck('question');

    foreach ($questions as $question) {
        $page->assertSee($question);
    }

    $page->click('Home')
        ->assertPathIs('/')
        ->click('Daily Dose')
        ->assertPathIs('/daily-dose');

    foreach ($questions as $question) {
        $page->assertSee($question);
    }

    expect(Test::count())->toBe(1);
    expect(Test::first()->id)->toBe($test->id);
    expect(Test::first()->questions_and_answers)->toBe($test->questions_and_answers);
});

it('submits correct answers and shows a full score', function () {
    createWordPool(10);

    $page = loginThroughBrowser()
        ->click('Daily Dose')
        ->assertSee('Submit');

    $test = Test::first();
    $questions = json_decode($test->questions_and_answers, true);

    foreach ($questions as $key => $item) {
        $page->fill('#question-'.$key, correctAnswerForQuestion($item));
    }

    handleConfirmationDialog($page);
    $page->click('Submit');

    $page->assertPathIs('/runs/'.$test->id)
        ->assertSee('Your score is: 3 / 3')
        ->assertSee('✔')
        ->assertDontSee('✘')
        ->assertSee('Click here to start a new run');

    expect($test->fresh()->score)->toBe(3);
});

it('submits incorrect answers and tracks them as wrong', function () {
    createWordPool(10);

    $page = loginThroughBrowser()
        ->click('Daily Dose')
        ->assertSee('Submit');

    $test = Test::first();
    $questions = json_decode($test->questions_and_answers, true);

    foreach ($questions as $key => $item) {
        $page->fill('#question-'.$key, 'definitely-wrong-answer');
    }

    handleConfirmationDialog($page);
    $page->click('Submit');

    $page->assertPathIs('/runs/'.$test->id)
        ->assertSee('Your score is: 0 / 3')
        ->assertSee('✘')
        ->assertDontSee('✔');

    $results = json_decode($test->fresh()->questions_and_answers, true);

    expect($test->fresh()->score)->toBe(0);

    foreach ($results as $item) {
        expect($item['correct'])->toBeFalse()
            ->and($item['answer'])->toBe('definitely-wrong-answer');
    }
});

it('starts a new test after finishing the previous one', function () {
    createWordPool(10);

    $page = loginThroughBrowser()
        ->click('Daily Dose')
        ->assertSee('Submit');

    $firstTest = Test::first();

    foreach (json_decode($firstTest->questions_and_answers, true) as $key => $item) {
        $page->fill('#question-'.$key, correctAnswerForQuestion($item));
    }

    handleConfirmationDialog($page);
    $page->click('Submit');

    $page->assertPathIs('/runs/'.$firstTest->id)
        ->click('Click here to start a new run')
        ->assertPathIs('/daily-dose')
        ->assertSee('Submit');

    expect(Test::count())->toBe(2);

    $secondTest = Test::query()->whereNull('score')->latest('id')->first();

    expect($secondTest->id)->not->toBe($firstTest->id);

    foreach (json_decode($secondTest->questions_and_answers, true) as $item) {
        $page->assertSee($item['question']);
    }
});
