<?php

use App\Models\Tag;
use App\Models\User;
use App\Models\Word;
use Laravel\Sanctum\Sanctum;

it('allows an authenticated user to edit a word', function () {
    Sanctum::actingAs(User::factory()->create());

    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $translation = $word->translations->first();
    $tag = Tag::factory()->create(['tag' => 'Morning']);

    $response = $this->putJson('/api/words/update/'.$word->id, wordPayload([
        'id' => $word->id,
        'word' => 'Buongiorno',
        'description' => 'A morning greeting',
        'tags' => [['id' => $tag->id]],
        'translations' => [
            [
                'id' => $translation->id,
                'translation' => 'Good morning',
                'test_help' => null,
            ],
        ],
    ]));

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'msg' => 'Word was updated!',
        ]);

    $this->assertDatabaseHas('words', [
        'id' => $word->id,
        'word' => 'Buongiorno',
        'description' => 'A morning greeting',
    ]);

    $this->assertDatabaseHas('translations', [
        'id' => $translation->id,
        'word_id' => $word->id,
        'translation' => 'Good morning',
    ]);

    expect($word->fresh()->tags->pluck('id')->all())->toContain($tag->id);
});

it('rejects guests from editing a word', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $translation = $word->translations->first();

    $this->putJson('/api/words/update/'.$word->id, wordPayload([
        'id' => $word->id,
        'word' => 'Buongiorno',
        'translations' => [
            [
                'id' => $translation->id,
                'translation' => 'Good morning',
                'test_help' => null,
            ],
        ],
    ]))->assertUnauthorized();

    $this->assertDatabaseHas('words', [
        'id' => $word->id,
        'word' => 'Ciao',
    ]);
});

it('rejects editing a word without the required word field', function () {
    Sanctum::actingAs(User::factory()->create());

    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $translation = $word->translations->first();

    $this->putJson('/api/words/update/'.$word->id, wordPayload([
        'id' => $word->id,
        'word' => null,
        'translations' => [
            [
                'id' => $translation->id,
                'translation' => 'Hello',
                'test_help' => null,
            ],
        ],
    ]))->assertUnprocessable()
        ->assertJsonValidationErrors(['word']);

    $this->assertDatabaseHas('words', [
        'id' => $word->id,
        'word' => 'Ciao',
    ]);
});

it('rejects editing a word without translations', function () {
    Sanctum::actingAs(User::factory()->create());

    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $payload = wordPayload([
        'id' => $word->id,
        'word' => 'Ciao',
    ]);
    unset($payload['translations']);

    $this->putJson('/api/words/update/'.$word->id, $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['translations']);

    $this->assertDatabaseHas('words', [
        'id' => $word->id,
        'word' => 'Ciao',
    ]);
});

it('rejects editing a word with an empty translation', function () {
    Sanctum::actingAs(User::factory()->create());

    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $translation = $word->translations->first();

    $this->putJson('/api/words/update/'.$word->id, wordPayload([
        'id' => $word->id,
        'word' => 'Ciao',
        'translations' => [
            [
                'id' => $translation->id,
                'translation' => '',
                'test_help' => null,
            ],
        ],
    ]))->assertUnprocessable()
        ->assertJsonValidationErrors(['translations.0.translation']);

    $this->assertDatabaseHas('translations', [
        'id' => $translation->id,
        'word_id' => $word->id,
        'translation' => 'Hello',
    ]);
});

it('rejects editing a word into a duplicate', function () {
    Sanctum::actingAs(User::factory()->create());

    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    createWordWithTranslationAndTag('Grazie', 'Thank you', 'Polite');
    $translation = $word->translations->first();

    $this->putJson('/api/words/update/'.$word->id, wordPayload([
        'id' => $word->id,
        'word' => 'Grazie',
        'translations' => [
            [
                'id' => $translation->id,
                'translation' => 'Hello',
                'test_help' => null,
            ],
        ],
    ]))->assertOk()
        ->assertJson([
            'status' => 'error',
            'msg' => 'This word (Grazie) already exists!',
        ]);

    $this->assertDatabaseHas('words', [
        'id' => $word->id,
        'word' => 'Ciao',
    ]);

    $this->assertDatabaseCount('words', 2);
});

it('allows keeping the same word name when updating other fields', function () {
    Sanctum::actingAs(User::factory()->create());

    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $translation = $word->translations->first();

    $this->putJson('/api/words/update/'.$word->id, wordPayload([
        'id' => $word->id,
        'word' => 'Ciao',
        'description' => 'Updated description',
        'translations' => [
            [
                'id' => $translation->id,
                'translation' => 'Hello',
                'test_help' => null,
            ],
        ],
    ]))->assertOk()
        ->assertJson([
            'status' => 'success',
        ]);

    $this->assertDatabaseHas('words', [
        'id' => $word->id,
        'word' => 'Ciao',
        'description' => 'Updated description',
    ]);
});

it('allows editing a word to add multiple translations', function () {
    Sanctum::actingAs(User::factory()->create());

    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $translation = $word->translations->first();

    $this->putJson('/api/words/update/'.$word->id, wordPayload([
        'id' => $word->id,
        'word' => 'Ciao',
        'translations' => [
            [
                'id' => $translation->id,
                'translation' => 'Hello',
                'test_help' => null,
            ],
            [
                'id' => 'temp-2',
                'translation' => 'Hi',
                'test_help' => null,
            ],
        ],
    ]))->assertOk()
        ->assertJson([
            'status' => 'success',
        ]);

    $word->refresh();

    expect($word->translations)->toHaveCount(2)
        ->and($word->translations->pluck('translation')->all())->toEqualCanonicalizing(['Hello', 'Hi']);
});

it('allows editing a word to add translation help', function () {
    Sanctum::actingAs(User::factory()->create());

    $word = createWordWithTranslationAndTag('Prego', 'You are welcome', 'Polite');
    $translation = $word->translations->first();

    $this->putJson('/api/words/update/'.$word->id, wordPayload([
        'id' => $word->id,
        'word' => 'Prego',
        'translations' => [
            [
                'id' => $translation->id,
                'translation' => 'You are welcome',
                'test_help' => 'Used as a polite reply',
            ],
        ],
    ]))->assertOk()
        ->assertJson([
            'status' => 'success',
        ]);

    $this->assertDatabaseHas('translations', [
        'id' => $translation->id,
        'word_id' => $word->id,
        'translation' => 'You are welcome',
        'test_help' => 'Used as a polite reply',
    ]);
});

it('returns not found when editing a missing word', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->putJson('/api/words/update/999999', wordPayload([
        'id' => 999999,
        'word' => 'Ciao',
    ]))->assertNotFound();
});
