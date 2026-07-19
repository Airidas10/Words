<?php

use App\Models\Tag;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\Sanctum;

it('renders the edit tag page for an authenticated user', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);
    Sanctum::actingAs(User::factory()->create());

    $this->get('/tags/edit/'.$tag->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TagCreator')
            ->where('tag.id', $tag->id)
            ->where('tag.tag', 'Greeting')
        );
});

it('redirects guests away from the edit tag page', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $this->get('/tags/edit/'.$tag->id)->assertRedirect('/login');
});

it('allows an authenticated user to edit a tag', function () {
    Sanctum::actingAs(User::factory()->create());
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $this->putJson('/api/tags/update/'.$tag->id, [
        'tag' => 'Polite',
    ])->assertOk()
        ->assertJson([
            'status' => 'success',
            'msg' => 'Tag was updated!',
            'data' => [
                'id' => $tag->id,
                'tag' => 'Polite',
            ],
        ]);

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'tag' => 'Polite',
    ]);

    $this->assertDatabaseMissing('tags', [
        'tag' => 'Greeting',
    ]);
});

it('rejects guests from editing a tag', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $this->putJson('/api/tags/update/'.$tag->id, [
        'tag' => 'Polite',
    ])->assertUnauthorized();

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'tag' => 'Greeting',
    ]);
});

it('rejects editing a tag without the required tag field', function () {
    Sanctum::actingAs(User::factory()->create());
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $this->putJson('/api/tags/update/'.$tag->id, [
        'tag' => null,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['tag']);

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'tag' => 'Greeting',
    ]);
});

it('rejects editing a tag with an empty name', function () {
    Sanctum::actingAs(User::factory()->create());
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $this->putJson('/api/tags/update/'.$tag->id, [
        'tag' => '',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['tag']);

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'tag' => 'Greeting',
    ]);
});

it('rejects editing a tag into a duplicate', function () {
    Sanctum::actingAs(User::factory()->create());
    $tag = Tag::factory()->create(['tag' => 'Greeting']);
    Tag::factory()->create(['tag' => 'Polite']);

    $this->putJson('/api/tags/update/'.$tag->id, [
        'tag' => 'Polite',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['tag']);

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'tag' => 'Greeting',
    ]);

    $this->assertDatabaseCount('tags', 2);
});

it('allows keeping the same tag name when updating', function () {
    Sanctum::actingAs(User::factory()->create());
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $this->putJson('/api/tags/update/'.$tag->id, [
        'tag' => 'Greeting',
    ])->assertOk()
        ->assertJson([
            'status' => 'success',
        ]);

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'tag' => 'Greeting',
    ]);

    $this->assertDatabaseCount('tags', 1);
});

it('returns not found when editing a missing tag', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->putJson('/api/tags/update/999999', [
        'tag' => 'Greeting',
    ])->assertNotFound();
});
