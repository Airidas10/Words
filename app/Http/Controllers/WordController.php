<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;
use App\Models\Word;

use App\Http\Requests\MeetingRequest;

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

    public function create()
    {
        \Log::debug("HERE");
        return Inertia::render('WordCreator', [
            'word' => null
        ]);
    }

    public function store(MeetingRequest $request)
    {
        // TODO
    }

    public function edit($id)
    {
        $word = Word::with('tags')->findOrFail($id);

        return Inertia::render('WordCreator', [
            'word' => $word
        ]);
    }

    public function update(MeetingRequest $request)
    {
        // TODO
    }

    public function destroy(MeetingRequest $request)
    {
        // TODO
    }
}
