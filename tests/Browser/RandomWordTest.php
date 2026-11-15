<?php

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;

it('can open random from the navigation', function () {
    createWordPool(30);

    $page = visit('/');

    $page->click('Random');

    waitForBrowser(fn () => $page
        ->assertPathIs('/random')
        ->assertSee('Next')
        ->assertNoJavascriptErrors());
});

it('shows a word from the pool on the random page', function () {
    $words = createWordPool(30);

    $page = visit('/random')
        ->assertPathIs('/random')
        ->assertSee('Next')
        ->assertSee('Show Translation')
        ->assertSee('*****');

    expect($words->pluck('word')->contains(visibleWordHeading($page)))->toBeTrue();
});

it('can navigate through several random words', function () {
    $words = createWordPool(30);
    $wordTexts = $words->pluck('word');

    $page = visit('/random')
        ->assertPathIs('/random')
        ->assertSee('Next')
        ->assertSee('Show Translation')
        ->assertSee('*****')
        ->assertDontSee('Hide Translation');

    expect($wordTexts->contains(visibleWordHeading($page)))->toBeTrue();

    // Next may repeat a word, so the heading cannot show that this visit finished.
    $randomVisits = 0;

    Event::listen(function (RequestHandled $event) use (&$randomVisits) {
        if ($event->request->is('random')) {
            $randomVisits++;
        }
    });

    foreach (range(1, 5) as $visit) {
        $finishedBefore = $randomVisits;

        $page->click('Next');

        waitForBrowser(function () use ($page, $wordTexts, $visit, &$randomVisits, $finishedBefore) {
            expect($randomVisits)->toBeGreaterThan($finishedBefore);

            $page->assertPathIs('/random')
                ->assertSee('Next')
                ->assertSee('Show Translation')
                ->assertSee('*****')
                ->assertDontSee('Hide Translation');

            expect($wordTexts->contains(visibleWordHeading($page)))
                ->toBeTrue("Expected a pool word on random visit #{$visit}");
        });
    }
});

it('hides translations after clicking next', function () {
    createWordPool(30);

    $page = visit('/random')
        ->assertSee('Show Translation')
        ->assertSee('*****')
        ->click('Show Translation')
        ->assertSee('Hide Translation');

    $page->click('Next');

    waitForBrowser(fn () => $page
        ->assertPathIs('/random')
        ->assertSee('Show Translation')
        ->assertSee('*****')
        ->assertDontSee('Hide Translation'));
});

it('selects the 100 newest words pool from the random picker', function () {
    createWordWithTranslationAndTag('Pizza', 'Pizza', 'Food');

    $page = visit('/random')
        ->assertPresent('@random-pool-select')
        ->click('@random-pool-select')
        ->assertSeeIn('@random-pool-panel', '100 Newest Words');

    $page->click('@random-pool-option-newest');

    waitForBrowser(fn () => $page
        ->assertPathIs('/random')
        ->assertQueryStringHas('pool', 'newest')
        ->assertSee('Pizza'));

    $page->click('Next');

    waitForBrowser(fn () => $page
        ->assertPathIs('/random')
        ->assertQueryStringHas('pool', 'newest')
        ->assertNoJavascriptErrors());
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
        ->assertSeeIn('@random-pool-panel', 'Food')
        ->assertDontSeeIn('@random-pool-panel', 'Colors');

    $page->click('@random-pool-option-tag-'.$tag->id);

    waitForBrowser(fn () => $page
        ->assertPathIs('/random')
        ->assertQueryStringHas('pool', 'tag')
        ->assertQueryStringHas('tag_id', (string) $tag->id)
        ->assertSee('Food')
        ->assertNoJavascriptErrors());
});

function visibleWordHeading(mixed $page): string
{
    return trim((string) $page->text('h1.text-3xl'));
}
