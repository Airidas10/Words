<?php

it('does not show delete on the create word page', function () {
    $page = loginThroughBrowser()
        ->click('Create New')
        ->assertSee('Create New Word')
        ->assertDontSee('Delete');
});

it('keeps the word when delete confirmation is cancelled', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $page = loginThroughBrowser()
        ->assertSee('Edit')
        ->click('Edit')
        ->assertSee('Edit Word')
        ->assertSee('Delete');

    handleConfirmationDialog($page, accept: false);
    $page->click('Delete');

    $page->assertPathIs('/words/edit/'.$word->id)
        ->assertSee('Edit Word');

    $this->assertDatabaseHas('words', [
        'id' => $word->id,
        'word' => 'Ciao',
    ]);
});

it('allows deleting the only remaining word', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $translationId = $word->translations->first()->id;

    $page = loginThroughBrowser()
        ->assertSee('Edit')
        ->click('Edit')
        ->assertSee('Edit Word');

    handleConfirmationDialog($page);
    $page->click('Delete');

    $page->assertPathIs('/')
        ->assertSee('Nothing to show here')
        ->assertDontSee('Ciao')
        ->assertDontSee('Hello');

    $this->assertDatabaseMissing('words', [
        'id' => $word->id,
    ]);

    $this->assertDatabaseMissing('translations', [
        'id' => $translationId,
    ]);

    $this->assertDatabaseCount('words', 0);
});

it('allows deleting a word when other words remain', function () {
    $wordToDelete = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    createWordWithTranslationAndTag('Grazie', 'Thank you', 'Polite');

    $page = loginThroughBrowser()
        ->assertSee('Ciao')
        ->navigate('/words/edit/'.$wordToDelete->id)
        ->assertSee('Edit Word');

    handleConfirmationDialog($page);
    $page->click('Delete');

    $page->assertPathIs('/')
        ->assertSee('Grazie')
        ->assertSee('Thank you')
        ->assertDontSee('Ciao')
        ->assertDontSee('Hello')
        ->assertDontSee('Nothing to show here');

    $this->assertDatabaseMissing('words', [
        'id' => $wordToDelete->id,
    ]);

    $this->assertDatabaseHas('words', [
        'word' => 'Grazie',
    ]);

    $this->assertDatabaseCount('words', 1);
});
