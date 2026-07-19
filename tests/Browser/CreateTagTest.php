<?php

use App\Models\Tag;

it('redirects guests away from the create tag page', function () {
    $page = visit('/tags/create');

    $page->assertPathIs('/login')
        ->assertSee('Login');
});

it('does not show delete on the create tag page', function () {
    $page = loginThroughBrowser()
        ->click('Tags')
        ->click('Create New')
        ->assertSee('Create New Tag')
        ->assertDontSee('Delete');
});

it('allows an authenticated user to create a tag', function () {
    $page = loginThroughBrowser()
        ->click('Tags')
        ->click('Create New')
        ->assertPathIs('/tags/create')
        ->assertSee('Create New Tag')
        ->fill('#tag', 'Greeting')
        ->click('Save');

    $page->assertPathIs('/tags')
        ->assertSee('Greeting')
        ->assertSee('0');

    $this->assertDatabaseHas('tags', [
        'tag' => 'Greeting',
    ]);

    $this->assertDatabaseCount('tags', 1);
});

it('rejects creating a tag with an empty name', function () {
    $page = loginThroughBrowser()
        ->click('Tags')
        ->click('Create New')
        ->assertSee('Create New Tag')
        ->fill('#tag', '')
        ->click('Save');

    $page->assertPathIs('/tags/create')
        ->assertSee('Create New Tag')
        ->assertSee('The tag field is required.')
        ->assertVisible('@tag-error');

    $this->assertDatabaseCount('tags', 0);
});

it('rejects creating a duplicate tag', function () {
    Tag::factory()->create(['tag' => 'Greeting']);

    $page = loginThroughBrowser()
        ->click('Tags')
        ->click('Create New')
        ->assertSee('Create New Tag')
        ->fill('#tag', 'Greeting')
        ->click('Save');

    $page->assertPathIs('/tags/create')
        ->assertSee('Create New Tag')
        ->assertSee('The tag has already been taken.')
        ->assertVisible('@tag-error');

    $this->assertDatabaseCount('tags', 1);
    $this->assertDatabaseHas('tags', [
        'tag' => 'Greeting',
    ]);
});
