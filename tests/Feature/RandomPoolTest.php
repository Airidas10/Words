<?php

use App\Models\Tag;
use App\Models\User;
use App\Models\Word;
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

it('picks only from the 100 newest words', function () {
    $oldest = createWordWithTranslationAndTag('OldestWord', 'Old', 'Greeting');
    $oldest->forceFill([
        'created_at' => now()->subYear(),
        'updated_at' => now()->subYear(),
    ])->save();

    $newest = collect(range(1, 100))->map(function (int $i) {
        $word = Word::factory()->create([
            'word' => 'Newest'.$i,
            'created_at' => now()->subMinutes(101 - $i),
        ]);
        $word->translations()->create([
            'translation' => 'Translation '.$i,
        ]);

        return 'Newest'.$i;
    });

    $this->get('/random?pool=newest')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('randomPool', 'newest')
            ->where('word.word', fn ($word) => $newest->contains($word))
        );
});

it('returns a null word when there are no newest words', function () {
    $this->get('/random?pool=newest')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('word', null)
            ->where('randomPool', 'newest')
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

it('skips tags on random partial reloads so the client can keep them', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    Tag::factory()->create(['tag' => 'Alpha']);

    $this->get('/random')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->has('tags')
            ->reloadOnly(['word', 'wordStats', 'randomPool', 'tagId'], function (Assert $page) {
                $page->missing('tags')
                    ->where('word.word', 'Ciao');
            })
        );
});
