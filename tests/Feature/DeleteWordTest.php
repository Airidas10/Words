<?php

use App\Models\User;
use App\Models\Word;
use Laravel\Sanctum\Sanctum;

it('allows an authenticated user to delete a word', function () {
    Sanctum::actingAs(User::factory()->create());

    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $translationId = $word->translations->first()->id;

    $this->deleteJson('/api/words/destroy/'.$word->id)
        ->assertOk()
        ->assertJson([
            'status' => 'success',
            'msg' => 'Word was deleted!',
        ]);

    $this->assertDatabaseMissing('words', [
        'id' => $word->id,
    ]);

    $this->assertDatabaseMissing('translations', [
        'id' => $translationId,
    ]);
});

it('rejects guests from deleting a word', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $this->deleteJson('/api/words/destroy/'.$word->id)
        ->assertUnauthorized();

    $this->assertDatabaseHas('words', [
        'id' => $word->id,
        'word' => 'Ciao',
    ]);
});

it('returns not found when deleting a missing word', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson('/api/words/destroy/999999')
        ->assertNotFound();
});

it('deletes only the targeted word when others remain', function () {
    Sanctum::actingAs(User::factory()->create());

    $wordToDelete = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $wordToKeep = createWordWithTranslationAndTag('Grazie', 'Thank you', 'Polite');

    $this->deleteJson('/api/words/destroy/'.$wordToDelete->id)
        ->assertOk()
        ->assertJson([
            'status' => 'success',
        ]);

    $this->assertDatabaseMissing('words', [
        'id' => $wordToDelete->id,
    ]);

    $this->assertDatabaseHas('words', [
        'id' => $wordToKeep->id,
        'word' => 'Grazie',
    ]);

    $this->assertDatabaseCount('words', 1);
});

it('cascades translation deletion when a word is deleted', function () {
    Sanctum::actingAs(User::factory()->create());

    $word = Word::factory()->create(['word' => 'Ciao']);
    $firstTranslation = $word->translations()->create(['translation' => 'Hello']);
    $secondTranslation = $word->translations()->create(['translation' => 'Hi']);

    $this->deleteJson('/api/words/destroy/'.$word->id)
        ->assertOk();

    $this->assertDatabaseMissing('translations', [
        'id' => $firstTranslation->id,
    ]);

    $this->assertDatabaseMissing('translations', [
        'id' => $secondTranslation->id,
    ]);

    $this->assertDatabaseCount('translations', 0);
});
