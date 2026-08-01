<?php

use App\Models\Tag;
use App\Models\Test;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Browser');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function createWordWithTranslationAndTag(string $wordText, string $translationText, string $tagText): Word
{
    $word = Word::factory()->create([
        'word' => $wordText,
    ]);

    $word->translations()->create([
        'translation' => $translationText,
    ]);

    $tag = Tag::firstOrCreate([
        'tag' => $tagText,
    ]);

    $word->tags()->syncWithoutDetaching([$tag->id]);

    return $word->fresh(['translations', 'tags']);
}

/**
 * @return \Illuminate\Support\Collection<int, Word>
 */
function createWordPool(int $count = 30): \Illuminate\Support\Collection
{
    return Word::factory()
        ->count($count)
        ->create()
        ->each(function (Word $word) {
            $word->translations()->create([
                'translation' => 'Translation for '.$word->word,
            ]);
        })
        ->fresh(['translations']);
}

function wordPayload(array $overrides = []): array
{
    return array_merge([
        'word' => 'Ciao',
        'description' => 'A common greeting',
        'tags' => [],
        'translations' => [
            [
                'translation' => 'Hello',
                'test_help' => null,
            ],
        ],
    ], $overrides);
}

function loginThroughBrowser(?User $user = null, string $username = 'editor', string $password = 'password')
{
    $user ??= User::factory()->create([
        'username' => $username,
        'password' => $password,
    ]);

    $token = $user->createToken($user->username.'-AuthToken')->plainTextToken;

    test()->actingAs($user);
    test()->withSession(['token' => $token]);

    return visit('/');
}

function correctAnswerForQuestion(array $item): string
{
    $word = Word::with('translations')->findOrFail($item['id']);

    return $item['type'] === 'w'
        ? $word->translations->first()->translation
        : $word->word;
}

function handleConfirmationDialog(mixed $page, bool $accept = true): void
{
    // For now, just ensure the confirm is accepted. Replace later, if on-brand modals are implemented.
    $page->script($accept
        ? '() => { window.confirm = () => true; }'
        : '() => { window.confirm = () => false; }'
    );
}

function createFinishedTest(User $user, int $score, int $numberOfQuestions = 3): Test
{
    return $user->tests()->create([
        'number_of_questions' => $numberOfQuestions,
        'questions_and_answers' => json_encode([
            '1' => [
                'id' => 1,
                'type' => 'w',
                'question' => 'Ciao',
                'answer' => 'Hello',
                'correct' => true,
                'correctAnswer' => 'hello',
                'help' => '',
            ],
        ]),
        'score' => $score,
    ]);
}

/**
 * @param  array<string, array<string, mixed>>  $questions  Keyed like "1", "2", …
 */
function createFinishedTestWithQuestions(User $user, array $questions, int $score): Test
{
    return $user->tests()->create([
        'number_of_questions' => count($questions),
        'questions_and_answers' => json_encode($questions),
        'score' => $score,
    ]);
}

function statsQuestionItem(int $wordId, string $type, bool $correct, string $question = 'Q'): array
{
    return [
        'id' => $wordId,
        'type' => $type,
        'question' => $question,
        'answer' => $correct ? 'ok' : 'nope',
        'correct' => $correct,
        'correctAnswer' => 'ok',
        'help' => '',
    ];
}

function createUnfinishedTest(User $user, int $numberOfQuestions = 3): Test
{
    return $user->tests()->create([
        'number_of_questions' => $numberOfQuestions,
        'questions_and_answers' => json_encode([
            '1' => [
                'id' => 1,
                'type' => 'w',
                'question' => 'Ciao',
                'answer' => '',
                'help' => '',
            ],
        ]),
        'score' => null,
    ]);
}

function attachStruggleWord(User $user, Word|int $word): void
{
    $wordId = $word instanceof Word ? $word->id : $word;

    $user->struggleWords()->syncWithoutDetaching([$wordId]);
}

/**
 * Record finished attempts so overall accuracy is correct/(correct+incorrect).
 */
function recordWordAttempts(User $user, Word $word, int $correct, int $incorrect): void
{
    $questions = [];
    $n = 1;

    for ($i = 0; $i < $correct; $i++) {
        $questions[(string) $n++] = statsQuestionItem($word->id, 'w', true, $word->word);
    }

    for ($i = 0; $i < $incorrect; $i++) {
        $questions[(string) $n++] = statsQuestionItem($word->id, 'w', false, $word->word);
    }

    if ($questions === []) {
        return;
    }

    createFinishedTestWithQuestions($user, $questions, score: $correct);
}
