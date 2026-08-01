<?php

use App\Models\User;
use App\Models\Word;
use App\Services\WordStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

it('aggregates overall and separate w and t tallies across finished tests', function () {
    $user = User::factory()->create();
    $word = Word::factory()->create(['word' => 'Ciao']);
    $word->translations()->create(['translation' => 'Hello']);

    createFinishedTestWithQuestions($user, [
        '1' => statsQuestionItem($word->id, 'w', true, 'Ciao'),
        '2' => statsQuestionItem($word->id, 't', false, 'Hello'),
    ], score: 1);

    createFinishedTestWithQuestions($user, [
        '1' => statsQuestionItem($word->id, 'w', false, 'Ciao'),
        '2' => statsQuestionItem($word->id, 't', true, 'Hello'),
    ], score: 1);

    $stats = app(WordStatsService::class)->forWord($user, $word->id);

    expect($stats)->not->toBeNull()
        ->and($stats['word_id'])->toBe($word->id)
        ->and($stats['overall'])->toMatchArray([
            'attempts' => 4,
            'correct' => 2,
            'incorrect' => 2,
            'accuracy' => 50.0,
        ])
        ->and($stats['w'])->toMatchArray([
            'attempts' => 2,
            'correct' => 1,
            'incorrect' => 1,
            'accuracy' => 50.0,
        ])
        ->and($stats['t'])->toMatchArray([
            'attempts' => 2,
            'correct' => 1,
            'incorrect' => 1,
            'accuracy' => 50.0,
        ]);
});

it('ignores unfinished tests when aggregating', function () {
    $user = User::factory()->create();
    $word = Word::factory()->create(['word' => 'Ciao']);
    $word->translations()->create(['translation' => 'Hello']);

    createFinishedTestWithQuestions($user, [
        '1' => statsQuestionItem($word->id, 'w', true),
    ], score: 1);

    createUnfinishedTest($user);
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

    $stats = app(WordStatsService::class)->forWord($user, $word->id);

    expect($stats['overall'])->toMatchArray([
        'attempts' => 1,
        'correct' => 1,
        'incorrect' => 0,
        'accuracy' => 100.0,
    ]);
});

it('isolates stats between users', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $word = Word::factory()->create(['word' => 'Ciao']);
    $word->translations()->create(['translation' => 'Hello']);

    createFinishedTestWithQuestions($userA, [
        '1' => statsQuestionItem($word->id, 'w', true),
    ], score: 1);

    createFinishedTestWithQuestions($userB, [
        '1' => statsQuestionItem($word->id, 'w', false),
        '2' => statsQuestionItem($word->id, 'w', false),
    ], score: 0);

    $service = app(WordStatsService::class);

    expect($service->forWord($userA, $word->id)['overall'])->toMatchArray([
        'attempts' => 1,
        'correct' => 1,
        'accuracy' => 100.0,
    ])->and($service->forWord($userB, $word->id)['overall'])->toMatchArray([
        'attempts' => 2,
        'correct' => 0,
        'accuracy' => 0.0,
    ]);
});

it('returns null when the user has never attempted the word', function () {
    $user = User::factory()->create();
    $word = Word::factory()->create(['word' => 'Ciao']);
    $word->translations()->create(['translation' => 'Hello']);

    expect(app(WordStatsService::class)->forWord($user, $word->id))->toBeNull();
});

it('omits missing word ids from stats without changing the tests table', function () {
    $user = User::factory()->create();
    $word = Word::factory()->create(['word' => 'Ciao']);
    $word->translations()->create(['translation' => 'Hello']);

    $test = createFinishedTestWithQuestions($user, [
        '1' => statsQuestionItem($word->id, 'w', true),
    ], score: 1);

    $storedQuestions = $test->questions_and_answers;
    $missingId = $word->id;

    $word->translations()->delete();
    $word->delete();

    $service = app(WordStatsService::class);

    expect($service->forWord($user, $missingId))->toBeNull()
        ->and($service->aggregate($user))->not->toHaveKey($missingId)
        ->and($test->fresh()->questions_and_answers)->toBe($storedQuestions);
});

it('skips malformed entries missing id or correct', function () {
    $user = User::factory()->create();
    $word = Word::factory()->create(['word' => 'Ciao']);
    $word->translations()->create(['translation' => 'Hello']);

    createFinishedTestWithQuestions($user, [
        '1' => statsQuestionItem($word->id, 'w', true),
        '2' => [
            'type' => 'w',
            'question' => 'Orphan',
            'answer' => 'x',
            'correct' => true,
            'correctAnswer' => 'x',
            'help' => '',
        ],
        '3' => [
            'id' => $word->id,
            'type' => 't',
            'question' => 'Hello',
            'answer' => 'Ciao',
            'help' => '',
        ],
    ], score: 1);

    $stats = app(WordStatsService::class)->forWord($user, $word->id);

    expect($stats['overall'])->toMatchArray([
        'attempts' => 1,
        'correct' => 1,
        'incorrect' => 0,
        'accuracy' => 100.0,
    ]);
});

it('returns stats only for requested word ids', function () {
    $user = User::factory()->create();
    $ciao = Word::factory()->create(['word' => 'Ciao']);
    $ciao->translations()->create(['translation' => 'Hello']);
    $grazie = Word::factory()->create(['word' => 'Grazie']);
    $grazie->translations()->create(['translation' => 'Thanks']);
    $prego = Word::factory()->create(['word' => 'Prego']);
    $prego->translations()->create(['translation' => 'Welcome']);

    createFinishedTestWithQuestions($user, [
        '1' => statsQuestionItem($ciao->id, 'w', true),
        '2' => statsQuestionItem($grazie->id, 'w', false),
        '3' => statsQuestionItem($prego->id, 't', true),
    ], score: 2);

    $map = app(WordStatsService::class)->forWordIds($user, [$ciao->id, $grazie->id]);

    expect($map)->toHaveKeys([$ciao->id, $grazie->id])
        ->and($map)->not->toHaveKey($prego->id)
        ->and($map[$ciao->id]['overall']['accuracy'])->toBe(100.0)
        ->and($map[$grazie->id]['overall']['accuracy'])->toBe(0.0);
});

it('serves cached aggregates until forget is called', function () {
    $user = User::factory()->create();
    $word = Word::factory()->create(['word' => 'Ciao']);
    $word->translations()->create(['translation' => 'Hello']);

    createFinishedTestWithQuestions($user, [
        '1' => statsQuestionItem($word->id, 'w', true),
    ], score: 1);

    $service = app(WordStatsService::class);

    expect($service->forWord($user, $word->id)['overall']['attempts'])->toBe(1);

    createFinishedTestWithQuestions($user, [
        '1' => statsQuestionItem($word->id, 'w', false),
    ], score: 0);

    expect($service->forWord($user, $word->id)['overall']['attempts'])->toBe(1);

    $service->forget($user);

    expect($service->forWord($user, $word->id)['overall'])->toMatchArray([
        'attempts' => 2,
        'correct' => 1,
        'incorrect' => 1,
        'accuracy' => 50.0,
    ]);
});
