<?php

use App\Models\Tag;
use App\Models\Word;

it('does not show edit to guests', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $page = visit('/');

    $page->assertSee('Ciao')
        ->assertDontSee('Edit')
        ->assertSee('Login');
});

it('redirects guests away from the edit word page', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $page = visit('/words/edit/'.$word->id);

    $page->assertPathIs('/login')
        ->assertSee('Login');
});

it('allows an authenticated user to edit a word', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $translationId = $word->translations->first()->id;

    $page = loginThroughBrowser()
        ->assertSee('Edit')
        ->click('Edit')
        ->assertSee('Edit Word')
        ->fill('word', 'Buongiorno')
        ->fill('#translation-'.$translationId, 'Good morning')
        ->fill('description', 'A morning greeting')
        ->click('Save');

    $page->assertPathIs('/')
        ->assertSee('Buongiorno')
        ->assertSee('Good morning')
        ->assertDontSee('Ciao')
        ->assertDontSee('Hello');

    $this->assertDatabaseHas('words', [
        'id' => $word->id,
        'word' => 'Buongiorno',
        'description' => 'A morning greeting',
    ]);

    $this->assertDatabaseHas('translations', [
        'id' => $translationId,
        'word_id' => $word->id,
        'translation' => 'Good morning',
    ]);
});

it('rejects editing a word with empty required fields', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $translationId = $word->translations->first()->id;

    $page = loginThroughBrowser()
        ->assertSee('Edit')
        ->click('Edit')
        ->assertSee('Edit Word')
        ->fill('word', '')
        ->fill('#translation-'.$translationId, '')
        ->click('Save');

    $page->assertPathIs('/words/edit/'.$word->id)
        ->assertSee('Edit Word')
        ->assertSee('The word field is required.')
        ->assertVisible('@word-error')
        ->assertSee('The translation field is required.')
        ->assertVisible('@translation-error-0');

    $this->assertDatabaseHas('words', [
        'id' => $word->id,
        'word' => 'Ciao',
    ]);

    $this->assertDatabaseHas('translations', [
        'id' => $translationId,
        'word_id' => $word->id,
        'translation' => 'Hello',
    ]);
});

it('rejects editing a word into a duplicate', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    createWordWithTranslationAndTag('Grazie', 'Thank you', 'Polite');

    $page = loginThroughBrowser()
        ->assertSee('Ciao')
        ->navigate('/words/edit/'.$word->id)
        ->assertSee('Edit Word')
        ->fill('word', 'Grazie')
        ->click('Save');

    $page->assertPathIs('/words/edit/'.$word->id)
        ->assertSee('Edit Word');

    $this->assertDatabaseHas('words', [
        'id' => $word->id,
        'word' => 'Ciao',
    ]);

    $this->assertDatabaseCount('words', 2);
});

it('allows editing a word to add multiple translations', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $translationId = $word->translations->first()->id;

    $page = loginThroughBrowser()
        ->assertSee('Edit')
        ->click('Edit')
        ->assertSee('Edit Word')
        ->click('Add more')
        ->fill('#translation-temp-2', 'Hi')
        ->click('Save');

    $page->assertPathIs('/')
        ->assertSee('Ciao')
        ->assertSee('Hello')
        ->assertSee('Hi');

    $word->refresh();

    expect($word->translations)->toHaveCount(2)
        ->and($word->translations->pluck('translation')->all())->toEqualCanonicalizing(['Hello', 'Hi']);

    $this->assertDatabaseHas('translations', [
        'id' => $translationId,
        'word_id' => $word->id,
        'translation' => 'Hello',
    ]);
});

it('allows editing a word to add translation help', function () {
    $word = createWordWithTranslationAndTag('Prego', 'You are welcome', 'Polite');

    $page = loginThroughBrowser()
        ->assertSee('Edit')
        ->click('Edit')
        ->assertSee('Edit Word')
        ->click('[title="Add translation help"]')
        ->fill('input[placeholder="Enter help text..."]', 'Used as a polite reply')
        ->click('Save');

    $page->assertPathIs('/')
        ->assertSee('Prego')
        ->assertSee('You are welcome');

    $this->assertDatabaseHas('translations', [
        'word_id' => $word->id,
        'translation' => 'You are welcome',
        'test_help' => 'Used as a polite reply',
    ]);
});

it('allows editing a word to attach tags', function () {
    $word = Word::factory()->create(['word' => 'Ciao']);
    $word->translations()->create(['translation' => 'Hello']);
    $greeting = Tag::factory()->create(['tag' => 'Greeting']);
    $polite = Tag::factory()->create(['tag' => 'Polite']);

    $page = loginThroughBrowser()
        ->assertSee('Edit')
        ->click('Edit')
        ->assertSee('Edit Word')
        ->assertSee('No tags yet')
        ->click('@open-tag-modal')
        ->assertSee('Select Tags')
        ->check('#tag-'.$greeting->id)
        ->check('#tag-'.$polite->id)
        ->click('Add Tags')
        ->assertDontSee('Select Tags')
        ->assertSee('Greeting')
        ->assertSee('Polite')
        ->assertDontSee('No tags yet')
        ->click('Save');

    $page->assertPathIs('/')
        ->assertSee('Ciao')
        ->assertSee('Greeting')
        ->assertSee('Polite');

    $word->refresh();

    expect($word->tags)->toHaveCount(2)
        ->and($word->tags->pluck('id')->all())->toEqualCanonicalizing([$greeting->id, $polite->id]);
});
