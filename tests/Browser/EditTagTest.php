<?php

use App\Models\Tag;

it('redirects guests away from the edit tag page', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $page = visit('/tags/edit/'.$tag->id);

    $page->assertPathIs('/login')
        ->assertSee('Login');
});

it('allows an authenticated user to edit a tag', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $page = loginThroughBrowser();

    $page->click('Tags');

    waitForBrowser(fn () => $page->assertSee('Greeting'));

    $page->click('Edit');

    waitForBrowser(fn () => $page
        ->assertPathIs('/tags/edit/'.$tag->id)
        ->assertSee('Edit Tag')
        ->assertSee('Delete'));

    $page->fill('#tag', 'Polite');
    $page->click('Save');

    waitForBrowser(fn () => $page
        ->assertPathIs('/tags')
        ->assertSee('Polite')
        ->assertDontSee('Greeting'));

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'tag' => 'Polite',
    ]);

    $this->assertDatabaseMissing('tags', [
        'tag' => 'Greeting',
    ]);
});

it('rejects editing a tag into a duplicate', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);
    Tag::factory()->create(['tag' => 'Polite']);

    $page = loginThroughBrowser();

    $page->click('Tags');

    waitForBrowser(fn () => $page->assertSee('Greeting'));

    $page->navigate('/tags/edit/'.$tag->id)
        ->assertSee('Edit Tag')
        ->fill('#tag', 'Polite');

    $page->click('Save');

    waitForBrowser(fn () => $page
        ->assertPathIs('/tags/edit/'.$tag->id)
        ->assertSee('Edit Tag'));

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'tag' => 'Greeting',
    ]);

    $this->assertDatabaseCount('tags', 2);
});

it('rejects editing a tag with an empty name', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $page = loginThroughBrowser();

    $page->click('Tags');

    waitForBrowser(fn () => $page->assertSee('Edit'));

    $page->click('Edit');

    waitForBrowser(fn () => $page->assertSee('Edit Tag'));

    $page->fill('#tag', '');
    $page->click('Save');

    waitForBrowser(fn () => $page
        ->assertPathIs('/tags/edit/'.$tag->id)
        ->assertSee('Edit Tag')
        ->assertSee('The tag field is required.')
        ->assertVisible('@tag-error'));

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'tag' => 'Greeting',
    ]);
});
