<?php

use App\Models\User;
use App\Models\Word;
use App\Services\WordStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
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

it('orders worstWords by lowest accuracy then highest attempts', function () {
    $user = User::factory()->create();
    $zeroFew = Word::factory()->create(['word' => 'ZeroFew']);
    $zeroFew->translations()->create(['translation' => 'A']);
    $zeroMany = Word::factory()->create(['word' => 'ZeroMany']);
    $zeroMany->translations()->create(['translation' => 'B']);
    $half = Word::factory()->create(['word' => 'Half']);
    $half->translations()->create(['translation' => 'C']);
    $perfect = Word::factory()->create(['word' => 'Perfect']);
    $perfect->translations()->create(['translation' => 'D']);

    recordWordAttempts($user, $zeroFew, correct: 0, incorrect: 1);
    recordWordAttempts($user, $zeroMany, correct: 0, incorrect: 3);
    recordWordAttempts($user, $half, correct: 1, incorrect: 1);
    recordWordAttempts($user, $perfect, correct: 2, incorrect: 0);

    $worst = app(WordStatsService::class)->worstWords($user);

    expect($worst->pluck('word_id')->values()->all())->toBe([
        $zeroMany->id,
        $zeroFew->id,
        $half->id,
        $perfect->id,
    ]);
});

it('limits worstWords to the requested count', function () {
    $user = User::factory()->create();
    $words = Word::factory()->count(4)->create()->each(function (Word $word) {
        $word->translations()->create(['translation' => 'T']);
    });

    foreach ($words->values() as $index => $word) {
        recordWordAttempts($user, $word, correct: $index, incorrect: 4 - $index);
    }

    $worst = app(WordStatsService::class)->worstWords($user, 2);

    expect($worst)->toHaveCount(2)
        ->and($worst->first()['word_id'])->toBe($words->values()->first()->id);
});

it('excludes words below stats_min_attempts from worstWords', function () {
    Config::set('words.stats_min_attempts', 2);

    $user = User::factory()->create();
    $enough = Word::factory()->create(['word' => 'Enough']);
    $enough->translations()->create(['translation' => 'A']);
    $tooFew = Word::factory()->create(['word' => 'TooFew']);
    $tooFew->translations()->create(['translation' => 'B']);

    recordWordAttempts($user, $enough, correct: 0, incorrect: 2);
    recordWordAttempts($user, $tooFew, correct: 0, incorrect: 1);

    $worst = app(WordStatsService::class)->worstWords($user);

    expect($worst->pluck('word_id')->all())->toBe([$enough->id]);
});

it('omits deleted words from worstWords', function () {
    $user = User::factory()->create();
    $kept = Word::factory()->create(['word' => 'Kept']);
    $kept->translations()->create(['translation' => 'A']);
    $gone = Word::factory()->create(['word' => 'Gone']);
    $gone->translations()->create(['translation' => 'B']);

    recordWordAttempts($user, $kept, correct: 1, incorrect: 0);
    recordWordAttempts($user, $gone, correct: 0, incorrect: 2);

    $gone->translations()->delete();
    $gone->delete();

    $worst = app(WordStatsService::class)->worstWords($user);

    expect($worst->pluck('word_id')->all())->toBe([$kept->id]);
});
