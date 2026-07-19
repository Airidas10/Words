<?php

use App\Models\Tag;
use App\Models\Word;

it('does not show create new to guests', function () {
    $page = visit('/');

    $page->assertDontSee('Create New')
        ->assertSee('Login');
});

it('redirects guests away from the create word page', function () {
    $page = visit('/words/create');

    $page->assertPathIs('/login')
        ->assertSee('Login');
});

it('allows an authenticated user to create a word', function () {
    $page = loginThroughBrowser()
        ->assertSee('Create New')
        ->click('Create New')
        ->assertSee('Create New Word')
        ->fill('word', 'Ciao')
        ->fill('#translation-temp-1', 'Hello')
        ->fill('description', 'A common greeting')
        ->click('Save');

    $page->assertPathIs('/')
        ->assertSee('Ciao')
        ->assertSee('Hello')
        ->assertSee('Hide Translation')
        ->assertDontSee('Nothing to show here');

    $this->assertDatabaseHas('words', [
        'word' => 'Ciao',
        'description' => 'A common greeting',
    ]);
});

it('rejects creating a word with empty required fields', function () {
    $page = loginThroughBrowser()
        ->click('Create New')
        ->assertSee('Create New Word')
        ->click('Save');

    $page->assertPathIs('/words/create')
        ->assertSee('Create New Word')
        ->assertSee('The word field is required.')
        ->assertVisible('@word-error')
        ->assertSee('The translation field is required.')
        ->assertVisible('@translation-error-0');

    $this->assertDatabaseCount('words', 0);
});

it('rejects creating a duplicate word', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $page = loginThroughBrowser()
        ->click('Create New')
        ->assertSee('Create New Word')
        ->fill('word', 'Ciao')
        ->fill('#translation-temp-1', 'Hello again')
        ->click('Save');

    $page->assertPathIs('/words/create')
        ->assertSee('Create New Word')
        ->assertSee('This word (Ciao) already exists!')
        ->assertVisible('@form-error');

    $this->assertDatabaseCount('words', 1);
    $this->assertDatabaseHas('words', [
        'word' => 'Ciao',
    ]);
});

it('allows creating a word with multiple translations', function () {
    $page = loginThroughBrowser()
        ->click('Create New')
        ->assertSee('Create New Word')
        ->fill('word', 'Ciao')
        ->fill('#translation-temp-1', 'Hello')
        ->click('Add more')
        ->fill('#translation-temp-2', 'Hi')
        ->click('Save');

    $page->assertPathIs('/')
        ->assertSee('Ciao')
        ->assertSee('Hello')
        ->assertSee('Hi');

    $this->assertDatabaseHas('words', [
        'word' => 'Ciao',
    ]);

    $word = Word::where('word', 'Ciao')->first();

    expect($word->translations)->toHaveCount(2)
        ->and($word->translations->pluck('translation')->all())->toEqualCanonicalizing(['Hello', 'Hi']);
});

it('allows creating a word with translation help', function () {
    $page = loginThroughBrowser()
        ->click('Create New')
        ->assertSee('Create New Word')
        ->fill('word', 'Prego')
        ->fill('#translation-temp-1', 'You are welcome')
        ->click('[title="Add translation help"]')
        ->fill('input[placeholder="Enter help text..."]', 'Used as a polite reply')
        ->click('Save');

    $page->assertPathIs('/')
        ->assertSee('Prego')
        ->assertSee('You are welcome');

    $this->assertDatabaseHas('words', [
        'word' => 'Prego',
    ]);

    $word = Word::where('word', 'Prego')->first();

    $this->assertDatabaseHas('translations', [
        'word_id' => $word->id,
        'translation' => 'You are welcome',
        'test_help' => 'Used as a polite reply',
    ]);
});

it('allows creating a word with tags', function () {
    $greeting = Tag::factory()->create(['tag' => 'Greeting']);
    $polite = Tag::factory()->create(['tag' => 'Polite']);

    $page = loginThroughBrowser()
        ->click('Create New')
        ->assertSee('Create New Word')
        ->assertSee('No tags yet')
        ->fill('word', 'Ciao')
        ->fill('#translation-temp-1', 'Hello')
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
        ->assertSee('Hello')
        ->assertSee('Greeting')
        ->assertSee('Polite');

    $word = Word::where('word', 'Ciao')->first();

    expect($word->tags)->toHaveCount(2)
        ->and($word->tags->pluck('id')->all())->toEqualCanonicalizing([$greeting->id, $polite->id]);
});

it('keeps tags unchanged when tag selection is cancelled', function () {
    $greeting = Tag::factory()->create(['tag' => 'Greeting']);

    $page = loginThroughBrowser()
        ->click('Create New')
        ->assertSee('Create New Word')
        ->assertSee('No tags yet')
        ->click('@open-tag-modal')
        ->assertSee('Select Tags')
        ->check('#tag-'.$greeting->id)
        ->click('Cancel')
        ->assertDontSee('Select Tags')
        ->assertSee('No tags yet')
        ->assertDontSee('Greeting');
});
