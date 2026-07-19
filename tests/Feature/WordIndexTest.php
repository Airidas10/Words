<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the home page for guests', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->has('wordsList.data', 0)
            ->where('totalWordCount', 0)
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
    $this->get('/export')->assertRedirect('/login');
});
