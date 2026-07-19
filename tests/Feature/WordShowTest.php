<?php

use App\Models\User;
use App\Models\Word;
use Inertia\Testing\AssertableInertia as Assert;

it('allows guests to view a word show page', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $word->update(['description' => 'A common greeting']);

    $this->get('/words/'.$word->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('word.id', $word->id)
            ->where('word.word', 'Ciao')
            ->where('word.description', 'A common greeting')
            ->has('word.translations', 1)
            ->where('word.translations.0.translation', 'Hello')
            ->has('word.tags', 1)
            ->where('word.tags.0.tag', 'Greeting')
        );
});

it('allows an authenticated user to view a word show page', function () {
    $word = createWordWithTranslationAndTag('Grazie', 'Thank you', 'Polite');

    $this->actingAs(User::factory()->create())
        ->get('/words/'.$word->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('word.word', 'Grazie')
            ->where('word.translations.0.translation', 'Thank you')
        );
});

it('shows a word with multiple translations', function () {
    $word = Word::factory()->create(['word' => 'Ciao']);
    $word->translations()->create(['translation' => 'Hello']);
    $word->translations()->create(['translation' => 'Hi']);

    $this->get('/words/'.$word->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->has('word.translations', 2)
            ->where('word.translations', function ($translations) {
                expect(collect($translations)->pluck('translation')->all())
                    ->toEqualCanonicalizing(['Hello', 'Hi']);

                return true;
            })
        );
});

it('shows a word with no tags', function () {
    $word = Word::factory()->create(['word' => 'Solo']);
    $word->translations()->create(['translation' => 'Alone']);

    $this->get('/words/'.$word->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('word.word', 'Solo')
            ->has('word.tags', 0)
        );
});

it('returns not found when viewing a missing word', function () {
    $this->get('/words/999999')->assertNotFound();
});
