<?php

it('searches from the home page and shows all matching words', function () {
    createWordWithTranslationAndTag('Saluto', 'Greeting', 'Social');
    createWordWithTranslationAndTag('Salutare', 'To greet', 'Social');
    createWordWithTranslationAndTag('Salute', 'Health', 'Body');
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $page = visit('/')
        ->fill('input[placeholder="Search..."]', 'Sal');

    $page->click('Go');

    waitForBrowser(fn () => $page
        ->assertPathIs('/search/global/Sal')
        ->assertSee('Search results for words containing')
        ->assertSee('Saluto')
        ->assertSee('Salutare')
        ->assertSee('Salute')
        ->assertDontSee('Ciao')
        ->assertDontSee('Nothing to show here')
        ->assertSee('Back to Words'));
});

it('shows an empty state when the search matches nothing', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    createWordWithTranslationAndTag('Grazie', 'Thank you', 'Polite');

    $page = visit('/')
        ->fill('input[placeholder="Search..."]', 'zzzznonexistent');

    $page->click('Go');

    waitForBrowser(fn () => $page
        ->assertPathIs('/search/global/zzzznonexistent')
        ->assertSee('Search results for words containing')
        ->assertSee('Nothing to show here')
        ->assertDontSee('Ciao')
        ->assertDontSee('Grazie'));
});

it('finds words by translation text', function () {
    createWordWithTranslationAndTag('Gatto', 'A catxyz pet', 'Animals');
    createWordWithTranslationAndTag('Gattino', 'Little catxyz', 'Animals');
    createWordWithTranslationAndTag('Cane', 'Dog', 'Animals');

    $page = visit('/')
        ->fill('input[placeholder="Search..."]', 'catxyz');

    $page->click('Go');

    waitForBrowser(fn () => $page
        ->assertPathIs('/search/global/catxyz')
        ->assertSee('Gatto')
        ->assertSee('Gattino')
        ->assertSee('A catxyz pet')
        ->assertSee('Little catxyz')
        ->assertDontSee('Cane')
        ->assertDontSee('Dog')
        ->assertDontSee('Nothing to show here'));
});

it('searches by tag from a word card', function () {
    createWordWithTranslationAndTag('Pizza', 'Pizza', 'Food');
    createWordWithTranslationAndTag('Pasta', 'Pasta', 'Food');
    createWordWithTranslationAndTag('Mela', 'Apple', 'Food');
    createWordWithTranslationAndTag('Cane', 'Dog', 'Animals');

    $page = visit('/');

    $page->click('Food');

    waitForBrowser(fn () => $page
        ->assertPathIs('/search/tag/Food')
        ->assertSee('Search results for a')
        ->assertSee('Food')
        ->assertSee('Pizza')
        ->assertSee('Pasta')
        ->assertSee('Mela')
        ->assertDontSee('Cane')
        ->assertDontSee('Nothing to show here'));
});

it('shows an empty state when a tag search matches nothing', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    visit('/search/tag/MissingTag')
        ->assertPathIs('/search/tag/MissingTag')
        ->assertSee('Nothing to show here')
        ->assertDontSee('Ciao');
});

it('can return to words from search results', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $page = visit('/')
        ->fill('input[placeholder="Search..."]', 'Ciao');

    $page->click('Go');

    waitForBrowser(fn () => $page
        ->assertPathIs('/search/global/Ciao')
        ->assertSee('Back to Words'));

    $page->click('← Back to Words');

    waitForBrowser(fn () => $page
        ->assertPathIs('/')
        ->assertSee('Ciao'));
});
