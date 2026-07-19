<?php

use App\Models\Tag;

it('keeps the tag when delete confirmation is cancelled', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $page = loginThroughBrowser()
        ->click('Tags')
        ->click('Edit')
        ->assertSee('Edit Tag')
        ->assertSee('Delete');

    handleConfirmationDialog($page, accept: false);
    $page->click('Delete');

    $page->assertPathIs('/tags/edit/'.$tag->id)
        ->assertSee('Edit Tag');

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'tag' => 'Greeting',
    ]);
});

it('allows deleting a tag', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);
    Tag::factory()->create(['tag' => 'Polite']);

    $page = loginThroughBrowser()
        ->click('Tags')
        ->assertSee('Greeting')
        ->navigate('/tags/edit/'.$tag->id)
        ->assertSee('Edit Tag');

    handleConfirmationDialog($page);
    $page->click('Delete');

    $page->assertPathIs('/tags')
        ->assertSee('Polite')
        ->assertDontSee('Greeting');

    $this->assertDatabaseMissing('tags', [
        'id' => $tag->id,
    ]);

    $this->assertDatabaseHas('tags', [
        'tag' => 'Polite',
    ]);

    $this->assertDatabaseCount('tags', 1);
});
