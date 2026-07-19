<?php

use App\Models\Tag;
use App\Models\User;
use App\Models\Word;
use Laravel\Sanctum\Sanctum;

it('allows an authenticated user to create a word', function () {
    Sanctum::actingAs(User::factory()->create());

    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $response = $this->postJson('/api/words/create', wordPayload([
        'tags' => [['id' => $tag->id]],
    ]));

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
        ]);

    $this->assertDatabaseHas('words', [
        'word' => 'Ciao',
        'description' => 'A common greeting',
    ]);

    $word = Word::where('word', 'Ciao')->first();

    expect($word->translations)->toHaveCount(1)
        ->and($word->translations->first()->translation)->toBe('Hello')
        ->and($word->tags->pluck('id')->all())->toContain($tag->id);
});

it('rejects guests from creating a word', function () {
    $this->postJson('/api/words/create', wordPayload())
        ->assertUnauthorized();

    $this->assertDatabaseCount('words', 0);
});

it('rejects a word without the required word field', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/words/create', wordPayload([
        'word' => null,
    ]))->assertUnprocessable()
        ->assertJsonValidationErrors(['word']);

    $this->assertDatabaseCount('words', 0);
});

it('rejects a word without translations', function () {
    Sanctum::actingAs(User::factory()->create());

    $payload = wordPayload();
    unset($payload['translations']);

    $this->postJson('/api/words/create', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['translations']);

    $this->assertDatabaseCount('words', 0);
});

it('rejects a word with an empty translation', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/words/create', wordPayload([
        'translations' => [
            ['translation' => '', 'test_help' => null],
        ],
    ]))->assertUnprocessable()
        ->assertJsonValidationErrors(['translations.0.translation']);

    $this->assertDatabaseCount('words', 0);
});

it('rejects a duplicate word', function () {
    Sanctum::actingAs(User::factory()->create());

    Word::factory()->create(['word' => 'Ciao']);

    $this->postJson('/api/words/create', wordPayload())
        ->assertOk()
        ->assertJson([
            'status' => 'error',
            'msg' => 'This word (Ciao) already exists!',
        ]);

    $this->assertDatabaseCount('words', 1);
});
