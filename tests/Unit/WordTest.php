<?php

use App\Models\Tag;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('stores a word with translations and tags', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    expect($word->word)->toBe('Ciao')
        ->and($word->translations)->toHaveCount(1)
        ->and($word->translations->first()->translation)->toBe('Hello')
        ->and($word->tags)->toHaveCount(1)
        ->and($word->tags->first()->tag)->toBe('Greeting');
});

it('orders related tags alphabetically by name', function () {
    $word = Word::factory()->create(['word' => 'Ciao']);
    $zebra = Tag::factory()->create(['tag' => 'Zebra']);
    $apple = Tag::factory()->create(['tag' => 'Apple']);
    $mango = Tag::factory()->create(['tag' => 'Mango']);

    $word->tags()->attach([$zebra->id, $apple->id, $mango->id]);

    expect($word->tags()->pluck('tag')->all())->toBe(['Apple', 'Mango', 'Zebra']);
});

it('cascades translation deletion when a word is deleted', function () {
    $word = Word::factory()->create(['word' => 'Ciao']);
    $first = $word->translations()->create(['translation' => 'Hello']);
    $second = $word->translations()->create(['translation' => 'Hi']);

    $word->delete();

    expect(Word::find($word->id))->toBeNull();
    $this->assertDatabaseMissing('translations', ['id' => $first->id]);
    $this->assertDatabaseMissing('translations', ['id' => $second->id]);
});

it('belongs to a word from a translation', function () {
    $word = Word::factory()->create(['word' => 'Ciao']);
    $translation = $word->translations()->create(['translation' => 'Hello']);

    expect($translation->word->id)->toBe($word->id)
        ->and($translation->word->word)->toBe('Ciao');
});
