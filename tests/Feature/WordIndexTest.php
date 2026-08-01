<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the home page for guests', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->has('wordsList.data', 0)
            ->where('totalWordCount', 0)
            ->where('wordStats', null)
        );
});

it('lists words on the home page', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    createWordWithTranslationAndTag('Grazie', 'Thank you', 'Polite');

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->has('wordsList.data', 2)
            ->where('totalWordCount', 2)
            ->where('wordsList.data', function ($words) {
                expect(collect($words)->pluck('word')->all())->toEqualCanonicalizing([
                    'Ciao',
                    'Grazie',
                ]);

                return true;
            })
        );
});

it('redirects guests from protected word routes to login', function () {
    $this->get('/words/create')->assertRedirect('/login');
    $this->get('/daily-dose')->assertRedirect('/login');
    $this->get('/tags')->assertRedirect('/login');
    $this->get('/my-tests')->assertRedirect('/login');
    $this->get('/my-struggles')->assertRedirect('/login');
    $this->get('/export')->assertRedirect('/login');
});

it('passes null wordStats on the home page for guests even when words exist', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    createFinishedTestWithQuestions(User::factory()->create(), [
        '1' => [
            'id' => $word->id,
            'type' => 'w',
            'question' => 'Ciao',
            'answer' => 'Hello',
            'correct' => true,
            'correctAnswer' => 'hello',
            'help' => '',
        ],
    ], score: 1);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->where('wordStats', null)
        );
});

it('passes overall wordStats for listed words to an authenticated user', function () {
    $user = User::factory()->create();
    $ciao = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $grazie = createWordWithTranslationAndTag('Grazie', 'Thank you', 'Polite');

    createFinishedTestWithQuestions($user, [
        '1' => [
            'id' => $ciao->id,
            'type' => 'w',
            'question' => 'Ciao',
            'answer' => 'Hello',
            'correct' => true,
            'correctAnswer' => 'hello',
            'help' => '',
        ],
        '2' => [
            'id' => $grazie->id,
            'type' => 'w',
            'question' => 'Grazie',
            'answer' => 'wrong',
            'correct' => false,
            'correctAnswer' => 'thank you',
            'help' => '',
        ],
    ], score: 1);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->where('wordStats', function ($wordStats) use ($ciao, $grazie) {
                // Inertia JSON turns map keys into strings on the wire.
                expect($wordStats[(string) $ciao->id]['overall'])
                    ->toMatchArray([
                        'attempts' => 1,
                        'correct' => 1,
                        'incorrect' => 0,
                    ])
                    ->and($wordStats[(string) $grazie->id]['overall'])
                    ->toMatchArray([
                        'attempts' => 1,
                        'correct' => 0,
                        'incorrect' => 1,
                    ]);

                return true;
            })
        );
});

it('excludes unfinished tests from home page wordStats', function () {
    $user = User::factory()->create();
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

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

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->where('wordStats', function ($wordStats) use ($word) {
                expect($wordStats[(string) $word->id] ?? null)->toBeNull();

                return true;
            })
        );
});
