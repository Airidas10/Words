<?php

use App\Models\Tag;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows an authenticated user to delete a tag', function () {
    Sanctum::actingAs(User::factory()->create());
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $this->deleteJson('/api/tags/destroy/'.$tag->id)
        ->assertOk()
        ->assertJson([
            'status' => 'success',
            'msg' => 'Tag was deleted!',
        ]);

    $this->assertDatabaseMissing('tags', [
        'id' => $tag->id,
    ]);
});

it('rejects guests from deleting a tag', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $this->deleteJson('/api/tags/destroy/'.$tag->id)
        ->assertUnauthorized();

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'tag' => 'Greeting',
    ]);
});

it('returns not found when deleting a missing tag', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson('/api/tags/destroy/999999')
        ->assertNotFound();
});

it('deletes only the targeted tag when others remain', function () {
    Sanctum::actingAs(User::factory()->create());
    $tagToDelete = Tag::factory()->create(['tag' => 'Greeting']);
    $tagToKeep = Tag::factory()->create(['tag' => 'Polite']);

    $this->deleteJson('/api/tags/destroy/'.$tagToDelete->id)
        ->assertOk()
        ->assertJson([
            'status' => 'success',
        ]);

    $this->assertDatabaseMissing('tags', [
        'id' => $tagToDelete->id,
    ]);

    $this->assertDatabaseHas('tags', [
        'id' => $tagToKeep->id,
        'tag' => 'Polite',
    ]);

    $this->assertDatabaseCount('tags', 1);
});

it('cascades pivot detachment when a tag is deleted', function () {
    Sanctum::actingAs(User::factory()->create());

    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $tag = $word->tags->first();

    $this->deleteJson('/api/tags/destroy/'.$tag->id)
        ->assertOk();

    $this->assertDatabaseMissing('tags', [
        'id' => $tag->id,
    ]);

    $this->assertDatabaseMissing('tag_word', [
        'tag_id' => $tag->id,
        'word_id' => $word->id,
    ]);

    $this->assertDatabaseHas('words', [
        'id' => $word->id,
        'word' => 'Ciao',
    ]);
});
