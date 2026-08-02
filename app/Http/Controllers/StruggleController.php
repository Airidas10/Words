<?php

namespace App\Http\Controllers;

use App\Models\Word;
use App\Models\User;
use App\Services\WordStatsService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class StruggleController extends Controller
{
    public function index(WordStatsService $wordStats): Response
    {
        $user = Auth::user();
        $words = $user->struggleWords()
            ->with(['translations', 'tags'])
            ->orderByPivot('updated_at', 'desc')
            ->get();

        User::applyStruggleFlags($user, $words);
        $words->each->makeHidden('pivot');

        return Inertia::render('MyStruggles', [
            'words' => $words,
            'wordStats' => $wordStats->forWordIds(
                $user,
                $words->pluck('id')->all(),
            ),
        ]);
    }

    public function proposals(WordStatsService $wordStats): JsonResponse
    {
        $user = Auth::user();
        $ids = $wordStats->worstWordsStats($user, 30)
            ->pluck('word_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $words = $this->wordsInOrder($ids);
        User::applyStruggleFlags($user, $words);
        $words->each->makeHidden('pivot');

        return response()->json([
            'status' => 'success',
            'words' => $words,
            'wordStats' => $wordStats->forWordIds($user, $ids),
        ]);
    }

    public function store(Word $word): JsonResponse
    {
        $user = Auth::user();
        $alreadyListed = $user->struggleWords()->where('words.id', $word->id)->exists();

        if (! $alreadyListed) {
            $cap = (int) config('words.struggles_cap');

            if ($user->struggleWords()->count() >= $cap) {
                return response()->json([
                    'msg' => 'Your Struggles list is full, learn existing words first',
                ], 422);
            }

            $user->struggleWords()->attach($word->id);
        }

        return response()->json([
            'status' => 'success',
            'msg' => 'Word added to My Struggles',
            'data' => ['word_id' => $word->id],
        ]);
    }

    public function destroy(Word $word): JsonResponse
    {
        $user = Auth::user();
        $user->struggleWords()->detach($word->id);

        return response()->json([
            'status' => 'success',
            'msg' => 'Word removed from My Struggles',
            'data' => null,
        ]);
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, Word>
     */
    private function wordsInOrder(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        $byId = Word::query()
            ->with(['translations', 'tags'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(fn (int $id) => $byId->get($id))
            ->filter()
            ->values();
    }
}
