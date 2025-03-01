<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Inertia\Inertia;

use Auth;

use App\Models\Word;
use App\Models\Tag;
use App\Models\Translation;

use App\Http\Requests\WordRequest;

class WordController extends Controller
{
    public function index()
    {
        $wordsPerPage = config('words.words_per_page');
        $words = Word::with('tags', 'translations')->orderBy('created_at', 'desc')->paginate($wordsPerPage);

        return Inertia::render('WordIndex', [
            'wordsList' => $words,
        ]);
    }

    public function show($id)
    {
        $word = Word::with('tags', 'translations')->findOrFail($id);

        return Inertia::render('Word', [
            'word' => $word,
        ]);
    }

    public function create()
    {
        $tags = Tag::orderBy('tag')->get();

        return Inertia::render('WordCreator', [
            'word' => null, 
            'tags' => $tags,
        ]);
    }

    public function store(WordRequest $request)
    {
        $response = ['status' => 'error', 'msg' => 'Something went wrong!', 'data' => null];

        if(Word::where('word', $request->word)->exists()){
            return ['status' => 'error', 'msg' => 'This word (' . $request->word . ') already exists!', 'data' => null];
        }

        DB::transaction(function() use ($request, &$response){
            $translationsCollection = collect();
            if($request->has('translations')){
                foreach ($request->translations as $translation){
                    $storedTranslation = Translation::make(['translation' => $translation['translation']]);
                    $translationsCollection->push($storedTranslation);
                }
            }

            $word = Word::create($request->only(['word', 'description']));
            if($word){
                $word->translations()->saveMany($translationsCollection);

                if($request->has('tags')){
                    $tagIds = collect($request->tags)->pluck('id');
                    $word->tags()->sync($tagIds);
                    $response = ['status' => 'success', 'msg' => 'Word was updated!', 'data' => $word];
                }
            }
        }, 5);

        return $response;
    }

    public function edit($id)
    {
        $word = Word::with('tags', 'translations')->findOrFail($id);
        $tags = Tag::orderBy('tag')->get();

        return Inertia::render('WordCreator', [
            'word' => $word,
            'tags' => $tags,
        ]);
    }

    public function update(WordRequest $request, $id)
    {        
        $response = ['status' => 'error', 'msg' => 'Something went wrong!', 'data' => null];

        if(Word::where('word', $request->word)->whereNot('id', $request->id)->exists()){
            return ['status' => 'error', 'msg' => 'This word (' . $request->word . ') already exists!', 'data' => null];
        }

        DB::transaction(function() use ($request, $id, &$response){
            $word = Word::findOrFail($id);
            if($word){
                $handledIds = [];
                foreach($request->translations as $translationData){
                    if(isset($translationData['id']) && is_numeric($translationData['id'])){
                        $translation = Translation::findOrFail($translationData['id']);
                        if ($translation->translation !== $translationData['translation']){
                            $translation->update([
                                'translation' => $translationData['translation'],
                            ]);
                        }
                        $handledIds[] = $translation->id;
                    } else if(isset($translationData['id']) && is_string($translationData['id'])){
                        $newTranslation = $word->translations()->create([
                            'translation' => $translationData['translation'],
                        ]);
                        $handledIds[] = $newTranslation->id;
                    }
                }

                $word->translations()->whereNotIn('id', $handledIds)->delete();

                $word->update($request->only(['word', 'description']));
                if($request->has('tags')){
                    $tagIds = collect($request->tags)->pluck('id');
                    $word->tags()->sync($tagIds);
                    $response = ['status' => 'success', 'msg' => 'Word was updated!', 'data' => $word];
                }
            }
        }, 5);

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

    public function getRandomWord(Request $request)
    {
        $word = Word::with('tags', 'translations')->inRandomOrder()->first();

        if($request->ajax() && $request->header('Accept') === 'application/json'){
            return ['status' => 'success', 'msg' => 'Data fetched successfully', 'data' => $word];
        } else{
            return Inertia::render('Word', [
                'word' => $word
            ]);
        }  
    }
}
