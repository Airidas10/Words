<?php

use Inertia\Testing\AssertableInertia as Assert;

it('allows guests to view the random word page', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $this->get('/random')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('word.word', 'Ciao')
            ->has('word.translations', 1)
            ->has('word.tags', 1)
        );
});

it('renders a word from the pool on the random page', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    createWordWithTranslationAndTag('Grazie', 'Thank you', 'Polite');

    $this->get('/random')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('word', function ($word) {
                expect(collect(['Ciao', 'Grazie'])->contains($word['word']))->toBeTrue()
                    ->and($word['translations'])->not->toBeEmpty()
                    ->and($word['tags'])->not->toBeEmpty();

                return true;
            })
        );
});

it('renders the random page with a null word when the pool is empty', function () {
    $this->get('/random')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Word')
            ->where('word', null)
        );
});

it('returns a random word as json for ajax requests', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $this->withHeaders([
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ])->get('/random')
        ->assertOk()
        ->assertJson([
            'status' => 'success',
            'msg' => 'Data fetched successfully',
            'data' => [
                'id' => $word->id,
                'word' => 'Ciao',
            ],
        ])
        ->assertJsonPath('data.translations.0.translation', 'Hello');
});
