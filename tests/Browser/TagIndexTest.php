<?php

use App\Models\Tag;

it('does not show tags navigation to guests', function () {
    $page = visit('/');

    $page->assertDontSee('Tags')
        ->assertSee('Login');
});

it('redirects guests away from the tags index and show pages', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    visit('/tags')
        ->assertPathIs('/login')
        ->assertSee('Login');

    visit('/tags/'.$tag->id)
        ->assertPathIs('/login')
        ->assertSee('Login');
});

it('lists tags with word counts for an authenticated user', function () {
    $greetingWord = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $greeting = $greetingWord->tags->first();
    $unused = Tag::factory()->create(['tag' => 'Unused']);

    $page = loginThroughBrowser()
        ->assertSee('Tags')
        ->click('Tags')
        ->assertPathIs('/tags')
        ->assertSee('Tags')
        ->assertSee('Create New')
        ->assertSee('Edit')
        ->assertSeeIn('@tag-name-'.$greeting->id, 'Greeting')
        ->assertSeeIn('@tag-word-count-'.$greeting->id, '1')
        ->assertSeeIn('@tag-name-'.$unused->id, 'Unused')
        ->assertSeeIn('@tag-word-count-'.$unused->id, '0');
});

it('shows a tag with its related words', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $tag = $word->tags->first();

    $page = loginThroughBrowser()
        ->navigate('/tags/'.$tag->id)
        ->assertPathIs('/tags/'.$tag->id)
        ->assertSee('Greeting')
        ->assertSee('Related Words:')
        ->assertSee('Ciao')
        ->assertSee('Hello')
        ->assertSee('← Back to Tags')
        ->click('← Back to Tags')
        ->assertPathIs('/tags');
});

it('shows an empty state when a tag has no related words', function () {
    $tag = Tag::factory()->create(['tag' => 'Unused']);

    $page = loginThroughBrowser()
        ->navigate('/tags/'.$tag->id)
        ->assertPathIs('/tags/'.$tag->id)
        ->assertSee('Unused')
        ->assertSee('No related words.')
        ->assertDontSee('Related Words:');
});
