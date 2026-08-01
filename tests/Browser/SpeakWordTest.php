<?php

it('shows a speak button on the word show page', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    visit('/words/'.$word->id)
        ->assertPathIs('/words/'.$word->id)
        ->assertPresent('@speak-word')
        ->assertNoJavascriptErrors();
});

it('requests italian speech synthesis when the speak button is clicked', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $page = visit('/words/'.$word->id)
        ->assertPresent('@speak-word');

    $page->script(<<<'JS'
        () => {
            window.__speakCalls = [];
            window.speechSynthesis.cancel = () => {};
            window.speechSynthesis.getVoices = () => [];
            window.speechSynthesis.speak = (utterance) => {
                window.__speakCalls.push({
                    text: utterance.text,
                    lang: utterance.lang,
                });
            };
        }
    JS);

    $page->click('@speak-word')
        ->assertNoJavascriptErrors();

    $calls = $page->script('() => window.__speakCalls');

    expect($calls)->toBe([
        [
            'text' => 'Ciao',
            'lang' => 'it-IT',
        ],
    ]);
});

it('shows a speak button on the random page', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    visit('/random')
        ->assertPathIs('/random')
        ->assertPresent('@speak-word')
        ->assertNoJavascriptErrors();
});

it('cancels speech when clicking next on the random page', function () {
    createWordPool(5);

    $page = visit('/random')
        ->assertPresent('@speak-word');

    $page->script(<<<'JS'
        () => {
            window.__cancelCalls = 0;
            window.speechSynthesis.cancel = () => {
                window.__cancelCalls += 1;
            };
            window.speechSynthesis.getVoices = () => [];
            window.speechSynthesis.speak = () => {};
        }
    JS);

    $page->click('@speak-word');

    expect($page->script('() => window.__cancelCalls'))->toBe(1);

    $page->click('Next')
        ->assertPathIs('/random')
        ->assertNoJavascriptErrors();

    expect($page->script('() => window.__cancelCalls'))->toBe(2);
});
