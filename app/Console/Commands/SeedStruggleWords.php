<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Word;
use App\Services\WordStatsService;
use Illuminate\Console\Command;

class SeedStruggleWords extends Command
{
    protected $signature = 'words:seed-struggle {--count=30 : Number of worst words to seed per user} {--dry-run : List words without saving}';

    protected $description = 'Seed each user\'s My Struggles list with their worst words by accuracy';

    public function handle(WordStatsService $wordStats): int
    {
        $count = (int) $this->option('count');
        $cap = (int) config('words.struggles_cap');

        if ($count > $cap) {
            $this->error("Count ({$count}) exceeds struggles CAP ({$cap}).");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $usersProcessed = 0;
        $wordsAttached = 0;

        foreach (User::query()->cursor() as $user) {
            $usersProcessed++;
            $existingIds = $user->struggleWordIds();
            $remaining = max(0, $cap - count($existingIds));

            $toAttachIds = $wordStats->worstWordsStats($user, $count)
                ->pluck('word_id')
                ->map(fn ($id) => (int) $id)
                ->reject(fn (int $id) => in_array($id, $existingIds, true))
                ->take($remaining)
                ->values()
                ->all();

            $labels = Word::query()
                ->whereIn('id', $toAttachIds)
                ->pluck('word', 'id');

            $wordList = collect($toAttachIds)
                ->map(fn (int $id) => $labels[$id] ?? "#{$id}")
                ->implode(', ');

            $this->line(sprintf(
                'User #%d (%s): %s',
                $user->id,
                $user->username,
                $wordList !== '' ? $wordList : '(none)',
            ));

            if ($dryRun || $toAttachIds === []) {
                continue;
            }

            $now = now();
            $total = count($toAttachIds);

            foreach ($toAttachIds as $index => $wordId) {
                // Newest updated_at first on My Struggles; give worst (index 0) the latest timestamp.
                // Sacrificing query efficiency to have them saved in desired order (worst to best)
                $timestamp = $now->copy()->addSeconds($total - $index);

                $user->struggleWords()->attach($wordId, [
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }

            $wordsAttached += $total;
        }

        if ($dryRun) {
            $this->info("Dry run complete. Users scanned: {$usersProcessed}. No changes saved.");
        } else {
            $this->info("Seeded My Struggles. Users: {$usersProcessed}. Words attached: {$wordsAttached}.");
        }

        return self::SUCCESS;
    }
}
