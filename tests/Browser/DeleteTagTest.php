<?php

use App\Models\Tag;

it('keeps the tag when delete confirmation is cancelled', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $page = loginThroughBrowser();

    $page->click('Tags');

    waitForBrowser(fn () => $page->assertSee('Edit'));

    $page->click('Edit');

    waitForBrowser(fn () => $page
        ->assertSee('Edit Tag')
        ->assertSee('Delete'));

    handleConfirmationDialog($page, accept: false);
    $page->click('Delete');

    waitForBrowser(fn () => $page
        ->assertPathIs('/tags/edit/'.$tag->id)
        ->assertSee('Edit Tag'));

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'tag' => 'Greeting',
    ]);
});

it('allows deleting a tag', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);
    Tag::factory()->create(['tag' => 'Polite']);

    $page = loginThroughBrowser();

    $page->click('Tags');

    waitForBrowser(fn () => $page->assertSee('Greeting'));

    $page->navigate('/tags/edit/'.$tag->id)
        ->assertSee('Edit Tag');

    handleConfirmationDialog($page);
    $page->click('Delete');

    waitForBrowser(fn () => $page
        ->assertPathIs('/tags')
        ->assertSee('Polite')
        ->assertDontSee('Greeting'));

    $this->assertDatabaseMissing('tags', [
        'id' => $tag->id,
    ]);

    $this->assertDatabaseHas('tags', [
        'tag' => 'Polite',
    ]);

    $this->assertDatabaseCount('tags', 1);
});
