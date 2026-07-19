<?php

use App\Models\Tag;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('can belong to many words', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);
    $ciao = Word::factory()->create(['word' => 'Ciao']);
    $salve = Word::factory()->create(['word' => 'Salve']);

    $tag->words()->attach([$ciao->id, $salve->id]);

    expect($tag->words)->toHaveCount(2)
        ->and($tag->words->pluck('id')->all())->toEqualCanonicalizing([$ciao->id, $salve->id]);
});

it('detaches from words when the tag is deleted', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $tag = $word->tags->first();

    $tag->delete();

    expect(Tag::find($tag->id))->toBeNull();
    $this->assertDatabaseMissing('tag_word', [
        'tag_id' => $tag->id,
        'word_id' => $word->id,
    ]);
    $this->assertDatabaseHas('words', [
        'id' => $word->id,
        'word' => 'Ciao',
    ]);
});
