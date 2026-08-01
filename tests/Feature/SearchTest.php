<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('finds all matching words by word text', function () {
    createWordWithTranslationAndTag('Saluto', 'Greeting', 'Social');
    createWordWithTranslationAndTag('Salutare', 'To greet', 'Social');
    createWordWithTranslationAndTag('Salute', 'Health', 'Body');
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $this->get('/search/global/Sal')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->where('isSearching', true)
            ->where('searchData.type', 'global')
            ->where('searchData.searchString', 'Sal')
            ->has('wordsList.data', 3)
            ->where('wordsList.data', function ($words) {
                expect(collect($words)->pluck('word')->all())->toEqualCanonicalizing([
                    'Saluto',
                    'Salutare',
                    'Salute',
                ]);

                return true;
            })
        );
});

it('finds all matching words by translation text', function () {
    createWordWithTranslationAndTag('Gatto', 'A catxyz pet', 'Animals');
    createWordWithTranslationAndTag('Gattino', 'Little catxyz', 'Animals');
    createWordWithTranslationAndTag('Cane', 'Dog', 'Animals');

    $this->get('/search/global/catxyz')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->where('isSearching', true)
            ->has('wordsList.data', 2)
            ->where('wordsList.data', function ($words) {
                expect(collect($words)->pluck('word')->all())->toEqualCanonicalizing([
                    'Gatto',
                    'Gattino',
                ]);

                return true;
            })
        );
});

it('shows no results when the global search matches nothing', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    createWordWithTranslationAndTag('Grazie', 'Thank you', 'Polite');

    $this->get('/search/global/zzzznonexistent')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->where('isSearching', true)
            ->has('wordsList.data', 0)
        );
});

it('finds all words with a matching tag', function () {
    createWordWithTranslationAndTag('Pizza', 'Pizza', 'Food');
    createWordWithTranslationAndTag('Pasta', 'Pasta', 'Food');
    createWordWithTranslationAndTag('Mela', 'Apple', 'Food');
    createWordWithTranslationAndTag('Cane', 'Dog', 'Animals');

    $this->get('/search/tag/Food')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->where('isSearching', true)
            ->where('searchData.type', 'tag')
            ->where('searchData.searchString', 'Food')
            ->has('wordsList.data', 3)
            ->where('wordsList.data', function ($words) {
                expect(collect($words)->pluck('word')->all())->toEqualCanonicalizing([
                    'Pizza',
                    'Pasta',
                    'Mela',
                ]);

                return true;
            })
        );
});

it('shows no results when the tag search matches nothing', function () {
    createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    $this->get('/search/tag/MissingTag')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->where('isSearching', true)
            ->has('wordsList.data', 0)
        );
});

it('does not include unrelated words in a tag search', function () {
    createWordWithTranslationAndTag('Rosso', 'Red', 'Colors');
    createWordWithTranslationAndTag('Blu', 'Blue', 'Colors');
    createWordWithTranslationAndTag('Grande', 'Big', 'Size');

    $this->get('/search/tag/Colors')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('wordsList.data', 2)
            ->where('wordsList.data', function ($words) {
                $wordNames = collect($words)->pluck('word')->all();

                expect($wordNames)
                    ->toEqualCanonicalizing(['Rosso', 'Blu'])
                    ->not->toContain('Grande');

                return true;
            })
        );
});

it('passes null wordStats on search results for guests', function () {
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');

    createFinishedTestWithQuestions(User::factory()->create(), [
        '1' => [
            'id' => $word->id,
            'type' => 'w',
            'question' => 'Ciao',
            'answer' => 'Hello',
            'correct' => true,
            'correctAnswer' => 'hello',
            'help' => '',
        ],
    ], score: 1);

    $this->get('/search/global/Ciao')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->where('isSearching', true)
            ->where('wordStats', null)
        );
});

it('passes overall wordStats on search results for an authenticated user', function () {
    $user = User::factory()->create();
    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    createWordWithTranslationAndTag('Ciabatta', 'Slipper', 'Food');

    createFinishedTestWithQuestions($user, [
        '1' => [
            'id' => $word->id,
            'type' => 'w',
            'question' => 'Ciao',
            'answer' => 'Hello',
            'correct' => true,
            'correctAnswer' => 'hello',
            'help' => '',
        ],
        '2' => [
            'id' => $word->id,
            'type' => 't',
            'question' => 'Hello',
            'answer' => 'wrong',
            'correct' => false,
            'correctAnswer' => 'ciao',
            'help' => '',
        ],
    ], score: 1);

    $this->actingAs($user)
        ->get('/search/global/Cia')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('WordIndex')
            ->where('isSearching', true)
            ->where('wordStats', function ($wordStats) use ($word) {
                $entry = $wordStats[(string) $word->id] ?? $wordStats[$word->id] ?? null;

                expect($entry['overall'] ?? null)->toMatchArray([
                    'attempts' => 2,
                    'correct' => 1,
                    'incorrect' => 1,
                ]);

                return true;
            })
        );
});
