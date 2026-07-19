<?php

use App\Models\Test;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Config::set('words.questions_per_test', 3);
});

it('redirects guests away from the daily dose page', function () {
    $this->get('/daily-dose')
        ->assertRedirect('/login');
});

it('starts a daily dose with the configured number of questions', function () {
    createWordPool(10);
    Sanctum::actingAs(User::factory()->create());

    $this->get('/daily-dose')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TestRun')
            ->has('testId')
            ->where('testJson', function ($testJson) {
                $questions = json_decode($testJson, true);

                expect($questions)->toHaveCount(3);

                return true;
            })
        );

    $this->assertDatabaseCount('tests', 1);
    $this->assertDatabaseHas('tests', [
        'number_of_questions' => 3,
        'score' => null,
    ]);
});

it('does not send correct answers to the frontend for an unfinished test', function () {
    createWordPool(10);
    Sanctum::actingAs(User::factory()->create());

    $assertNoAnswersLeaked = function (string $testJson): bool {
        foreach (json_decode($testJson, true) as $item) {
            expect($item)->not->toHaveKeys(['correctAnswer', 'correct'])
                ->and($item['answer'])->toBe('')
                ->and($item)->toHaveKeys(['id', 'type', 'question']);
        }

        return true;
    };

    $this->get('/daily-dose')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TestRun')
            ->where('testJson', $assertNoAnswersLeaked)
        );

    $this->get('/daily-dose')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TestRun')
            ->where('testJson', $assertNoAnswersLeaked)
        );
});

it('reuses the same unfinished test after leaving and returning', function () {
    createWordPool(10);
    Sanctum::actingAs(User::factory()->create());

    $this->get('/daily-dose')->assertOk();

    $test = Test::first();
    $originalQuestions = $test->questions_and_answers;

    $this->get('/daily-dose')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TestRun')
            ->where('testId', $test->id)
            ->where('testJson', $originalQuestions)
        );

    expect(Test::count())->toBe(1);
});

it('creates a new test after the previous one is finished', function () {
    createWordPool(10);
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->get('/daily-dose')->assertOk();
    $firstTest = Test::first();

    $questions = json_decode($firstTest->questions_and_answers, true);
    $answers = collect($questions)->map(function (array $item) {
        return [
            'id' => $item['id'],
            'type' => $item['type'],
            'question' => $item['question'],
            'answer' => correctAnswerForQuestion($item),
            'help' => $item['help'] ?? '',
        ];
    })->all();

    $this->postJson('/api/tests/submit', [
        'testId' => $firstTest->id,
        'testData' => $answers,
    ])->assertOk()
        ->assertJson([
            'status' => 'success',
        ]);

    expect($firstTest->fresh()->score)->not->toBeNull();

    $this->get('/daily-dose')->assertOk();

    expect(Test::count())->toBe(2);
    expect(Test::latest('id')->first()->id)->not->toBe($firstTest->id);
    expect(Test::latest('id')->first()->score)->toBeNull();
});

it('tracks a fully correct submission', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $other = createWordWithTranslationAndTag('Grazie', 'Thank you', 'Polite');

    $testData = [
        '1' => [
            'id' => $word->id,
            'type' => 'w',
            'question' => 'Ciao',
            'answer' => '',
            'help' => '',
        ],
        '2' => [
            'id' => $other->id,
            'type' => 't',
            'question' => 'Thank you',
            'answer' => '',
            'help' => '',
        ],
    ];

    $test = $user->tests()->create([
        'number_of_questions' => 2,
        'questions_and_answers' => json_encode($testData),
        'score' => null,
    ]);

    $this->postJson('/api/tests/submit', [
        'testId' => $test->id,
        'testData' => [
            '1' => array_merge($testData['1'], ['answer' => 'Hello']),
            '2' => array_merge($testData['2'], ['answer' => 'Grazie']),
        ],
    ])->assertOk()
        ->assertJson([
            'status' => 'success',
            'msg' => 'Test was submitted!',
        ]);

    $test->refresh();
    $results = json_decode($test->questions_and_answers, true);

    expect($test->score)->toBe(2)
        ->and($results['1']['correct'])->toBeTrue()
        ->and($results['1']['answer'])->toBe('Hello')
        ->and($results['1']['correctAnswer'])->toBe('hello')
        ->and($results['2']['correct'])->toBeTrue()
        ->and($results['2']['answer'])->toBe('Grazie')
        ->and($results['2']['correctAnswer'])->toBe('grazie');
});

it('tracks a fully incorrect submission', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $testData = [
        '1' => [
            'id' => $word->id,
            'type' => 'w',
            'question' => 'Ciao',
            'answer' => '',
            'help' => '',
        ],
    ];

    $test = $user->tests()->create([
        'number_of_questions' => 1,
        'questions_and_answers' => json_encode($testData),
        'score' => null,
    ]);

    $this->postJson('/api/tests/submit', [
        'testId' => $test->id,
        'testData' => [
            '1' => array_merge($testData['1'], ['answer' => 'wrong']),
        ],
    ])->assertOk();

    $test->refresh();
    $results = json_decode($test->questions_and_answers, true);

    expect($test->score)->toBe(0)
        ->and($results['1']['correct'])->toBeFalse()
        ->and($results['1']['answer'])->toBe('wrong')
        ->and($results['1']['correctAnswer'])->toBe('hello');
});

it('tracks mixed correct and incorrect answers', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $ciao = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $grazie = createWordWithTranslationAndTag('Grazie', 'Thank you', 'Polite');
    $prego = createWordWithTranslationAndTag('Prego', 'You are welcome', 'Polite');

    $testData = [
        '1' => [
            'id' => $ciao->id,
            'type' => 'w',
            'question' => 'Ciao',
            'answer' => '',
            'help' => '',
        ],
        '2' => [
            'id' => $grazie->id,
            'type' => 't',
            'question' => 'Thank you',
            'answer' => '',
            'help' => '',
        ],
        '3' => [
            'id' => $prego->id,
            'type' => 'w',
            'question' => 'Prego',
            'answer' => '',
            'help' => '',
        ],
    ];

    $test = $user->tests()->create([
        'number_of_questions' => 3,
        'questions_and_answers' => json_encode($testData),
        'score' => null,
    ]);

    $this->postJson('/api/tests/submit', [
        'testId' => $test->id,
        'testData' => [
            '1' => array_merge($testData['1'], ['answer' => 'hello']),
            '2' => array_merge($testData['2'], ['answer' => 'wrong']),
            '3' => array_merge($testData['3'], ['answer' => 'You are welcome']),
        ],
    ])->assertOk();

    $test->refresh();
    $results = json_decode($test->questions_and_answers, true);

    expect($test->score)->toBe(2)
        ->and($results['1']['correct'])->toBeTrue()
        ->and($results['2']['correct'])->toBeFalse()
        ->and($results['3']['correct'])->toBeTrue();
});

it('rejects guests from submitting a test', function () {
    $user = User::factory()->create();
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $test = $user->tests()->create([
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

    $this->postJson('/api/tests/submit', [
        'testId' => $test->id,
        'testData' => [
            '1' => [
                'id' => $word->id,
                'type' => 'w',
                'question' => 'Ciao',
                'answer' => 'Hello',
                'help' => '',
            ],
        ],
    ])->assertUnauthorized();

    expect($test->fresh()->score)->toBeNull();
});

it('forbids viewing another users finished test', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $test = $owner->tests()->create([
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

    Sanctum::actingAs($other);

    $this->get('/runs/'.$test->id)
        ->assertForbidden();
});
