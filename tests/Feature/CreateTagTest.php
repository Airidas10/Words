<?php

use App\Models\Tag;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\Sanctum;

it('renders the create tag page for an authenticated user', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->get('/tags/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TagCreator')
            ->where('tag', null)
        );
});

it('redirects guests away from the create tag page', function () {
    $this->get('/tags/create')->assertRedirect('/login');
});

it('allows an authenticated user to create a tag', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/tags/create', [
        'tag' => 'Greeting',
    ])->assertOk()
        ->assertJson([
            'status' => 'success',
            'msg' => 'Tag was created!',
            'data' => [
                'tag' => 'Greeting',
            ],
        ]);

    $this->assertDatabaseHas('tags', [
        'tag' => 'Greeting',
    ]);

    $this->assertDatabaseCount('tags', 1);
});

it('rejects guests from creating a tag', function () {
    $this->postJson('/api/tags/create', [
        'tag' => 'Greeting',
    ])->assertUnauthorized();

    $this->assertDatabaseCount('tags', 0);
});

it('rejects a tag without the required tag field', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/tags/create', [
        'tag' => null,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['tag']);

    $this->assertDatabaseCount('tags', 0);
});

it('rejects a tag with an empty name', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/tags/create', [
        'tag' => '',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['tag']);

    $this->assertDatabaseCount('tags', 0);
});

it('rejects a duplicate tag', function () {
    Sanctum::actingAs(User::factory()->create());
    Tag::factory()->create(['tag' => 'Greeting']);

    $this->postJson('/api/tags/create', [
        'tag' => 'Greeting',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['tag']);

    $this->assertDatabaseCount('tags', 1);
    $this->assertDatabaseHas('tags', [
        'tag' => 'Greeting',
    ]);
});
