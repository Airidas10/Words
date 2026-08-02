<?php

it('can open random from the navigation', function () {
    createWordPool(30);

    $page = visit('/')
        ->click('Random')
        ->assertPathIs('/random')
        ->assertSee('Next')
        ->assertNoJavascriptErrors();
});

it('shows a word from the pool on the random page', function () {
    $words = createWordPool(30);
    $wordTexts = $words->pluck('word');

    $page = visit('/random')
        ->assertPathIs('/random')
        ->assertSee('Next')
        ->assertSee('Hide Translation');

    $content = $page->content();

    expect($wordTexts->first(fn (string $word) => str_contains($content, $word)))->not->toBeNull();
});

it('can navigate through several random words', function () {
    $words = createWordPool(30);
    $wordTexts = $words->pluck('word');

    $page = visit('/random')
        ->assertPathIs('/random')
        ->assertSee('Next')
        ->assertSee('Hide Translation');

    foreach (range(1, 5) as $visit) {
        $content = $page->content();

        expect($wordTexts->contains(fn (string $word) => str_contains($content, $word)))
            ->toBeTrue("Expected a pool word on random visit #{$visit}");

        $page->assertPathIs('/random')
            ->assertSee('Next');

        if ($visit === 1) {
            $page->assertSee('Hide Translation');
        } else {
            // Next commits showTranslation=false; proves the click advanced the run.
            $page->assertSee('Show Translation')
                ->assertSee('*****')
                ->assertDontSee('Hide Translation');
        }

        $page->click('Next');
    }

    $page->assertPathIs('/random')
        ->assertSee('Next')
        ->assertSee('Show Translation')
        ->assertSee('*****');

    $finalContent = $page->content();

    expect($wordTexts->contains(fn (string $word) => str_contains($finalContent, $word)))->toBeTrue();
});

it('hides translations after clicking next', function () {
    createWordPool(30);

    $page = visit('/random')
        ->assertSee('Hide Translation')
        ->click('Next')
        ->assertPathIs('/random')
        ->assertSee('Show Translation')
        ->assertSee('*****')
        ->assertDontSee('Hide Translation')
        ->click('Show Translation')
        ->assertSee('Hide Translation')
        ->assertDontSee('*****');
});

it('filters tags and selects a tag pool from the random picker', function () {
    $food = createWordWithTranslationAndTag('Pizza', 'Pizza', 'Food');
    createWordWithTranslationAndTag('Rosso', 'Red', 'Colors');
    $tag = $food->tags->first();

    $page = visit('/random')
        ->assertPresent('@random-pool-select')
        ->click('@random-pool-select')
        ->assertPresent('@random-pool-panel')
        ->assertPresent('@random-pool-filter')
        ->fill('@random-pool-filter', 'Foo')
        ->assertSee('Food')
        ->assertDontSee('Colors')
        ->click('@random-pool-option-tag-'.$tag->id)
        ->assertPathIs('/random')
        ->assertQueryStringHas('pool', 'tag')
        ->assertQueryStringHas('tag_id', (string) $tag->id)
        ->assertSee('Food')
        ->assertNoJavascriptErrors();
});
