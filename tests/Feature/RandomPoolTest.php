<?php

use App\Models\Tag;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\Sanctum;

it('keeps full random as the default pool', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $this->get('/random')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('word.word', 'Ciao')
            ->where('randomPool', function ($pool) {
                expect($pool === null || $pool === 'all')->toBeTrue();

                return true;
            })
        );
});

it('picks only from the authenticated users struggles pool', function () {
    $user = User::factory()->create();
    $struggle = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    createWordWithTranslationAndTag('Grazie', 'Thanks', 'Polite');
    attachStruggleWord($user, $struggle);

    Sanctum::actingAs($user);

    $this->get('/random?pool=struggles')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('word.word', 'Ciao')
            ->where('randomPool', 'struggles')
        );
});

it('returns a null word when the struggles pool is empty', function () {
    $user = User::factory()->create();
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    Sanctum::actingAs($user);

    $this->get('/random?pool=struggles')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('word', null)
            ->where('randomPool', 'struggles')
        );
});

it('falls back to the full pool when a guest requests struggles', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $this->get('/random?pool=struggles')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('word.word', 'Ciao')
        );
});

it('picks only words for the selected tag pool', function () {
    $greeting = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    createWordWithTranslationAndTag('Grazie', 'Thanks', 'Polite');
    $tag = $greeting->tags->first();

    $this->get('/random?pool=tag&tag_id='.$tag->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('word.word', 'Ciao')
            ->where('randomPool', 'tag')
        );
});

it('passes tags sorted alphabetically for the random dropdown', function () {
    Tag::factory()->create(['tag' => 'Zebra']);
    Tag::factory()->create(['tag' => 'Alpha']);

    $this->get('/random')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('tags', function ($tags) {
                expect(collect($tags)->pluck('tag')->all())->toBe(['Alpha', 'Zebra']);

                return true;
            })
        );
});
