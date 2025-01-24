<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;
use App\Models\Word;

class SearchController extends Controller
{
    public function search(Request $request, $type, $searchString=null)
    {

        $words = Word::where('word', 'like', '%' . $searchString . '%')->orWhere('translation', 'like', '%' . $searchString . '%')->get();

        if($request->header('X-Requested-With') === 'XMLHttpRequest'){
            \Log::debug("IF");
            return ['status' => 'success', 'msg' => 'Data fetched successfully', 'data' => $words];
        } else{
            \Log::debug("ELSE");
            return Inertia::render('Index', [
                'wordsList' => $words
            ]);
        }
    }
}
