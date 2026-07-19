<?php

use App\Models\Tag;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\Sanctum;

it('lists tags with word counts for an authenticated user', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    Tag::factory()->create(['tag' => 'Unused']);
    Sanctum::actingAs(User::factory()->create());

    $this->get('/tags')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TagsIndex')
            ->has('tags', 2)
            ->where('tags', function ($tags) {
                $tags = collect($tags);

                expect($tags->pluck('tag')->all())->toEqualCanonicalizing(['Greeting', 'Unused'])
                    ->and($tags->firstWhere('tag', 'Greeting')['words_count'])->toBe(1)
                    ->and($tags->firstWhere('tag', 'Unused')['words_count'])->toBe(0);

                return true;
            })
        );
});

it('shows a tag with its related words', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $tag = $word->tags->first();
    Sanctum::actingAs(User::factory()->create());

    $this->get('/tags/'.$tag->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tag')
            ->where('tag.id', $tag->id)
            ->where('tag.tag', 'Greeting')
            ->has('tag.words', 1)
            ->where('tag.words.0.word', 'Ciao')
            ->where('tag.words.0.translations.0.translation', 'Hello')
        );
});

it('shows a tag with no related words', function () {
    $tag = Tag::factory()->create(['tag' => 'Unused']);
    Sanctum::actingAs(User::factory()->create());

    $this->get('/tags/'.$tag->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tag')
            ->where('tag.id', $tag->id)
            ->where('tag.tag', 'Unused')
            ->has('tag.words', 0)
        );
});

it('returns not found when viewing a missing tag', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->get('/tags/999999')->assertNotFound();
});

it('redirects guests away from the tag show page', function () {
    $tag = Tag::factory()->create(['tag' => 'Greeting']);

    $this->get('/tags/'.$tag->id)->assertRedirect('/login');
});
