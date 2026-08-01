<?php

namespace App\Http\Controllers;

use App\Models\Word;
use App\Services\WordStatsService;
use Auth;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class StruggleController extends Controller
{
    public function index(WordStatsService $wordStats): Response
    {
        $user = Auth::user();
        $words = $user->struggleWords()
            ->with(['translations', 'tags'])
            ->get();

        return Inertia::render('MyStruggles', [
            'words' => $words,
            'wordStats' => $wordStats->forWordIds(
                $user,
                $words->pluck('id')->all(),
            ),
            'struggleWordIds' => $user->struggleWordIds(),
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
}
