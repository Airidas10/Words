<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use stdClass;

use Inertia\Inertia;
use Auth;

use App\Models\Word;
use App\Services\WordStatsService;

class SearchController extends Controller
{
    public function search(Request $request, $type, $searchString = null, WordStatsService $wordStats)
    {
        $searchData = new stdClass();
        $searchData->type = $type;
        $searchData->searchString = $searchString;

        $wordsPerPage = config('words.words_per_page');

        switch ($type) {
            case 'global':
                $words = Word::with('tags', 'translations')->where('word', 'like', '%' . $searchString . '%')->orWhereHas('translations', function ($q) use ($searchString) {
                    $q->where('translation', 'like', '%' . $searchString . '%');
                })->orderBy('created_at', 'desc')->paginate($wordsPerPage);

                return Inertia::render('WordIndex', [
                    'wordsList' => $words,
                    'isSearching' => true,
                    'searchData' => $searchData,
                    'wordStats' => $wordStats->forWordIdsIfAuthenticated(
                        Auth::user(),
                        $words->getCollection()->pluck('id')->all(),
                    ),
                ]);

            case 'tag':
                $words = Word::with('tags', 'translations')->whereHas('tags', function ($q) use ($searchString) {
                    $q->where('tag', $searchString);
                })->orderBy('created_at', 'desc')->paginate($wordsPerPage);

                return Inertia::render('WordIndex', [
                    'wordsList' => $words,
                    'isSearching' => true,
                    'searchData' => $searchData,
                    'wordStats' => $wordStats->forWordIdsIfAuthenticated(
                        Auth::user(),
                        $words->getCollection()->pluck('id')->all(),
                    ),
                ]);

            default:
                return Inertia::render('Error', [
                    'message' => 'Something went wrong. Please try again.',
                ]);
        }
    }
}
