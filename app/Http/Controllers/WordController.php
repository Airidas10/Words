<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;
use App\Models\Word;

use App\Http\Requests\WordRequest;

class WordController extends Controller
{
    public function index()
    {
        $words = Word::with('tags')->orderBy('created_at', 'desc')->get();

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
        return Inertia::render('WordCreator', [
            'word' => null
        ]);
    }

    public function store(WordRequest $request)
    {
        $response = ['status' => 'error', 'msg' => 'Something went wrong!', 'data' => null];

        $word = Word::create($request->all());

        $response = ['status' => 'success', 'msg' => 'Word was created!', 'data' => $word];

        return $response;
    }

    public function edit($id)
    {
        $word = Word::with('tags')->findOrFail($id);

        return Inertia::render('WordCreator', [
            'word' => $word
        ]);
    }

    public function update(WordRequest $request, $id)
    {
        $response = ['status' => 'error', 'msg' => 'Something went wrong!', 'data' => null];

        $word = Word::findOrFail($id);

        if($word){
            $word->update($request->all());
        }

        $response = ['status' => 'success', 'msg' => 'Word was updated!', 'data' => $word];

        return $response;
    }

    public function destroy(WordRequest $request, $id)
    {
        $response = ['status' => 'error', 'msg' => 'Something went wrong!', 'data' => null];

        $word = Word::findOrFail($id);

        if($word){
            $word->delete();
        }

        $response = ['status' => 'success', 'msg' => 'Word was deleted!', 'data' => null];

        return $response;
    }
}
