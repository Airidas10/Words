<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\Sanctum;

it('redirects guests away from my tests', function () {
    $this->get('/my-tests')->assertRedirect('/login');
});

it('shows an empty my tests page for an authenticated user', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->get('/my-tests')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('MyTests')
            ->where('totalTests', 0)
            ->where('averageScore', null)
            ->has('userTests.data', 0)
        );
});

it('lists only the authenticated users finished tests', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Sanctum::actingAs($user);

    $finished = createFinishedTest($user, score: 2, numberOfQuestions: 3);
    createUnfinishedTest($user, numberOfQuestions: 3);
    createFinishedTest($other, score: 3, numberOfQuestions: 3);

    $this->get('/my-tests')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('MyTests')
            ->where('totalTests', 1)
            ->has('userTests.data', 1)
            ->where('userTests.data.0.id', $finished->id)
            ->where('userTests.data.0.score', 2)
            ->where('userTests.data.0.number_of_questions', 3)
        );
});

it('shows the total and average score across finished tests', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    createFinishedTest($user, score: 3, numberOfQuestions: 3);
    createFinishedTest($user, score: 1, numberOfQuestions: 3);

    $this->get('/my-tests')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('MyTests')
            ->where('totalTests', 2)
            ->has('userTests.data', 2)
            ->where('averageScore', function ($averageScore) {
                expect((float) $averageScore)->toBe(2.0);

                return true;
            })
        );
});

it('paginates finished tests', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    foreach (range(1, 11) as $score) {
        createFinishedTest($user, score: min($score, 3), numberOfQuestions: 3);
    }

    $this->get('/my-tests')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('MyTests')
            ->where('totalTests', 11)
            ->has('userTests.data', 10)
            ->where('userTests.per_page', 10)
            ->where('userTests.last_page', 2)
        );

    $this->get('/my-tests?page=2')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('MyTests')
            ->has('userTests.data', 1)
            ->where('userTests.current_page', 2)
        );
});

it('orders finished tests by newest first', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $older = createFinishedTest($user, score: 1, numberOfQuestions: 3);
    $older->forceFill([
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ])->save();

    $newer = createFinishedTest($user, score: 3, numberOfQuestions: 3);

    $this->get('/my-tests')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('MyTests')
            ->where('userTests.data.0.id', $newer->id)
            ->where('userTests.data.1.id', $older->id)
        );
});
