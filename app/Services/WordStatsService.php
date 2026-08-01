<?php

namespace App\Services;

use App\Models\User;
use App\Models\Word;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class WordStatsService
{
    public function aggregate(User $user): Collection
    {
        $cached = Cache::rememberForever(
            $this->cacheKey($user),
            fn () => $this->compute($user)->all(),
        );

        return $this->onlyExistingWords(collect($cached));
    }

    public function forWord(User $user, int $wordId): ?array
    {
        return $this->aggregate($user)->get($wordId);
    }

    /**
     * Stats for one word when logged in; null for guests.
     */
    public function forWordIfAuthenticated(?User $user, int $wordId): ?array
    {
        if ($user === null) {
            return null;
        }

        return $this->forWord($user, $wordId);
    }

    /**
     * @param  array<int>  $wordIds
     * @return array<int, array<string, mixed>>
     */
    public function forWordIds(User $user, array $wordIds): array
    {
        $all = $this->aggregate($user);
        $result = [];

        foreach ($wordIds as $wordId) {
            $wordId = (int) $wordId;
            $stats = $all->get($wordId);

            if ($stats !== null) {
                $result[$wordId] = $stats;
            }
        }

        return $result;
    }

    /**
     * Stats map for a list of words when logged in; null for guests.
     *
     * @param  array<int>  $wordIds
     * @return array<int, array<string, mixed>>|null
     */
    public function forWordIdsIfAuthenticated(?User $user, array $wordIds): ?array
    {
        if ($user === null) {
            return null;
        }

        return $this->forWordIds($user, $wordIds);
    }

    public function forget(User $user): void
    {
        Cache::forget($this->cacheKey($user));
    }

    private function cacheKey(User $user): string
    {
        return 'user_word_stats:'.$user->id;
    }

    private function compute(User $user): Collection
    {
        $tallies = [];

        $tests = $user->tests()
            ->finished()
            ->get(['questions_and_answers']);

        foreach ($tests as $test) {
            $questions = json_decode($test->questions_and_answers, true);

            if (! is_array($questions)) {
                continue;
            }

            foreach ($questions as $entry) {
                if (! is_array($entry) || ! $this->isValidEntry($entry)) {
                    continue;
                }

                $wordId = (int) $entry['id'];
                $type = $entry['type'] === 't' ? 't' : 'w';
                $correct = (bool) $entry['correct'];

                if (! isset($tallies[$wordId])) {
                    $tallies[$wordId] = $this->emptyWordStats($wordId);
                }

                $this->recordAttempt($tallies[$wordId]['overall'], $correct);
                $this->recordAttempt($tallies[$wordId][$type], $correct);
            }
        }

        return collect($tallies);
    }

    private function isValidEntry(array $entry): bool
    {
        return array_key_exists('id', $entry)
            && $entry['id'] !== null
            && array_key_exists('correct', $entry)
            && $entry['correct'] !== null
            && in_array($entry['type'] ?? null, ['w', 't'], true);
    }

    private function emptyWordStats(int $wordId): array
    {
        return [
            'word_id' => $wordId,
            'overall' => $this->emptyBucket(),
            'w' => $this->emptyBucket(),
            't' => $this->emptyBucket(),
        ];
    }

    private function emptyBucket(): array
    {
        return [
            'attempts' => 0,
            'correct' => 0,
            'incorrect' => 0,
            'accuracy' => 0.0,
        ];
    }

    private function recordAttempt(array &$bucket, bool $correct): void
    {
        $bucket['attempts']++;

        if ($correct) {
            $bucket['correct']++;
        } else {
            $bucket['incorrect']++;
        }

        $bucket['accuracy'] = round(
            ($bucket['correct'] / $bucket['attempts']) * 100,
            1,
        );
    }

    private function onlyExistingWords(Collection $stats): Collection
    {
        // Cache/JSON can turn word ids into strings; normalize to ints for lookups.
        $byWordId = [];
        foreach ($stats as $wordId => $wordStats) {
            $byWordId[(int) $wordId] = $wordStats;
        }

        $stats = collect($byWordId);

        if ($stats->isEmpty()) {
            return $stats;
        }

        $existingIds = Word::query()
            ->whereIn('id', $stats->keys())
            ->pluck('id')
            ->all();

        return $stats->only($existingIds);
    }
}
