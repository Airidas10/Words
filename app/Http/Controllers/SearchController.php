<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use stdClass;

use Inertia\Inertia;
use App\Models\Word;

class SearchController extends Controller
{
    public function search(Request $request, $type, $searchString=null)
    {
        $searchData = new stdClass();
        $searchData->type = $type;
        $searchData->searchString = $searchString;

        switch ($type) {
            case 'global':
                $words = Word::with('tags')->where('word', 'like', '%' . $searchString . '%')->orWhere('translation', 'like', '%' . $searchString . '%')->get();
                return Inertia::render('WordIndex', [
                    'wordsList' => $words,
                    'isSearching' => true,
                    'searchData' => $searchData,
                ]);
                break;

            case 'tag':
                $words = Word::with('tags')->whereHas('tags', function($q) use($searchString){
                    $q->where('tag', $searchString);
                })->get();

                return Inertia::render('WordIndex', [
                    'wordsList' => $words,
                    'isSearching' => true,
                    'searchData' => $searchData,
                ]);
                break;
            
            default:
                return Inertia::render('Error', [
                    'message' => 'Something went wrong. Please try again.',
                ]);
                break;
        }
    }
}
