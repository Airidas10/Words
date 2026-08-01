<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\Sanctum;

it('redirects guests from the my-struggles page to login', function () {
    $this->get('/my-struggles')->assertRedirect('/login');
});

it('shows the my-struggles page with words and wordStats for an authenticated user', function () {
    $user = User::factory()->create();
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    recordWordAttempts($user, $word, correct: 1, incorrect: 1);
    attachStruggleWord($user, $word);

    Sanctum::actingAs($user);

    $this->get('/my-struggles')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('MyStruggles')
            ->has('words', 1)
            ->where('words.0.word', 'Ciao')
            ->where('words.0.translations.0.translation', 'Hello')
            ->where('struggleWordIds', [$word->id])
            ->where('wordStats', function ($wordStats) use ($word) {
                expect($wordStats[(string) $word->id]['overall'])->toMatchArray([
                    'attempts' => 2,
                    'correct' => 1,
                    'incorrect' => 1,
                ]);

                return true;
            })
        );
});

it('rejects guests from adding a word to struggles', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $this->postJson('/api/struggles/'.$word->id)
        ->assertUnauthorized();

    $this->assertDatabaseMissing('user_word', [
        'word_id' => $word->id,
    ]);
});

it('allows an authenticated user to add a word to struggles', function () {
    $user = User::factory()->create();
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    Sanctum::actingAs($user);

    $this->postJson('/api/struggles/'.$word->id)
        ->assertOk()
        ->assertJson([
            'status' => 'success',
        ]);

    $this->assertDatabaseHas('user_word', [
        'user_id' => $user->id,
        'word_id' => $word->id,
    ]);
});

it('is safe to add a word that is already in struggles', function () {
    $user = User::factory()->create();
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    attachStruggleWord($user, $word);

    Sanctum::actingAs($user);

    $this->postJson('/api/struggles/'.$word->id)
        ->assertOk();

    expect($user->fresh()->struggleWords->count())->toBe(1);
});

it('rejects adding when the struggles list is at CAP', function () {
    Config::set('words.struggles_cap', 2);

    $user = User::factory()->create();
    $first = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $second = createWordWithTranslationAndTag('Grazie', 'Thanks', 'Polite');
    $third = createWordWithTranslationAndTag('Prego', 'Welcome', 'Polite');

    attachStruggleWord($user, $first);
    attachStruggleWord($user, $second);

    Sanctum::actingAs($user);

    $this->postJson('/api/struggles/'.$third->id)
        ->assertStatus(422)
        ->assertJson([
            'msg' => 'Your Struggles list is full, learn existing words first',
        ]);

    $this->assertDatabaseMissing('user_word', [
        'user_id' => $user->id,
        'word_id' => $third->id,
    ]);
});

it('allows an authenticated user to remove a word from struggles', function () {
    $user = User::factory()->create();
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    attachStruggleWord($user, $word);

    Sanctum::actingAs($user);

    $this->deleteJson('/api/struggles/'.$word->id)
        ->assertOk()
        ->assertJson([
            'status' => 'success',
        ]);

    $this->assertDatabaseMissing('user_word', [
        'user_id' => $user->id,
        'word_id' => $word->id,
    ]);
});

it('rejects guests from removing a word from struggles', function () {
    $user = User::factory()->create();
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    attachStruggleWord($user, $word);

    $this->deleteJson('/api/struggles/'.$word->id)
        ->assertUnauthorized();

    $this->assertDatabaseHas('user_word', [
        'user_id' => $user->id,
        'word_id' => $word->id,
    ]);
});

it('isolates struggles between users', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    Sanctum::actingAs($userA);
    $this->postJson('/api/struggles/'.$word->id)->assertOk();

    expect($userA->fresh()->struggleWords->pluck('id')->all())->toBe([$word->id])
        ->and($userB->fresh()->struggleWords->pluck('id')->all())->toBe([]);
});

it('passes struggleWordIds on the home page for an authenticated user', function () {
    $user = User::factory()->create();
    $inList = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    createWordWithTranslationAndTag('Grazie', 'Thanks', 'Polite');
    attachStruggleWord($user, $inList);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->where('struggleWordIds', [$inList->id])
        );
});

it('passes struggleWordIds on the word show page for an authenticated user', function () {
    $user = User::factory()->create();
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    attachStruggleWord($user, $word);

    $this->actingAs($user)
        ->get('/words/'.$word->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('struggleWordIds', [$word->id])
        );
});

it('passes null struggleWordIds on the home page for guests', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->where('struggleWordIds', null)
        );
});

it('passes struggleWordIds on the tag show page for an authenticated user', function () {
    $user = User::factory()->create();
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $tag = $word->tags->first();
    attachStruggleWord($user, $word);

    Sanctum::actingAs($user);

    $this->get('/tags/'.$tag->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tag')
            ->where('struggleWordIds', [$word->id])
        );
});
