<?php

use App\Models\Test;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('only includes finished tests in the finished scope', function () {
    $user = User::factory()->create();
    $word = Word::factory()->create(['word' => 'Ciao']);

    $finished = $user->tests()->create([
        'number_of_questions' => 1,
        'questions_and_answers' => json_encode([
            '1' => [
                'id' => $word->id,
                'type' => 'w',
                'question' => 'Ciao',
                'answer' => 'Hello',
                'correct' => true,
                'correctAnswer' => 'hello',
                'help' => '',
            ],
        ]),
        'score' => 1,
    ]);

    $user->tests()->create([
        'number_of_questions' => 1,
        'questions_and_answers' => json_encode([
            '1' => [
                'id' => $word->id,
                'type' => 'w',
                'question' => 'Ciao',
                'answer' => '',
                'help' => '',
            ],
        ]),
        'score' => null,
    ]);

    $finishedIds = Test::finished()->pluck('id');

    expect($finishedIds)->toHaveCount(1)
        ->and($finishedIds->first())->toBe($finished->id);
});

it('formats timestamps in the Europe/Vilnius timezone', function () {
    Carbon::setTestNow(Carbon::parse('2024-01-15 12:00:00', 'UTC'));

    $user = User::factory()->create();
    $test = $user->tests()->create([
        'number_of_questions' => 1,
        'questions_and_answers' => json_encode([]),
        'score' => 1,
    ]);

    expect($test->created_at)->toBe('2024-01-15 14:00:00')
        ->and($test->updated_at)->toBe('2024-01-15 14:00:00');

    Carbon::setTestNow();
});

it('belongs to a user through the tests relationship', function () {
    $user = User::factory()->create();
    $test = createFinishedTest($user, score: 2, numberOfQuestions: 3);

    expect($user->tests)->toHaveCount(1)
        ->and($user->tests->first()->id)->toBe($test->id);
});
