<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;
use App\Models\Word;

class WordController extends Controller
{
    public function index()
    {
        $words = Word::with('tags')->get();

        return Inertia::render('Index', [
            'words' => $words
        ]);
    }

    public function show($id)
    {
        $word = Word::with('tags')->findOrFail($id);

        return Inertia::render('Word', [
            'word' => $word,
        ]);
    }
}
