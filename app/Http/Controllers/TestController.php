<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;
use Auth;
use \Carbon\Carbon;

use App\Models\Word;
use App\Models\Translation;

class TestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $lastTest = $user->tests()->latest()->first();
        $questionsPerTest = config('words.questions_per_test');

        // If no last test found, or last one is not from today and has a score, create new test...
        if(!$lastTest || ($lastTest && !$lastTest->created_at->isToday() && $lastTest->score !== null)){
            $testData = collect();
            $usedWordIds = [];
            for($i = 1; $i <= $questionsPerTest; $i++){
                $dataObj = collect();
                if(random_int(0, 1)){
                    $wordObj = Word::select('id', 'word')->whereNotIn('id', $usedWordIds)->inRandomOrder()->first();
                    $dataObj->put('id', $wordObj->id);
                    $dataObj->put('type', 'w');
                    $dataObj->put('question', $wordObj->word);
                    $dataObj->put('answer', '');
                } else{
                    $translationObj = Translation::select('word_id', 'translation')->whereNotIn('word_id', $usedWordIds)->inRandomOrder()->first();
                    $dataObj->put('id', $translationObj->word_id);
                    $dataObj->put('type', 't');
                    $dataObj->put('question', $translationObj->translation);
                    $dataObj->put('answer', '');
                }
                $usedWordIds[] = $dataObj->get('id');
                $testData->put($i, $dataObj);
            }

            $testJson = json_encode($testData);
            // Persist to db
            $testObj = $user->tests()->create(['user_id' => $user->id, 'number_of_questions' => $questionsPerTest, 'questions_and_answers' => $testJson]);
            $testId = $testObj->id;
        } else{
            $testJson = $lastTest->questions_and_answers;
            $testId = $lastTest->id;
        }

        return Inertia::render('TestIndex', [
            'testId' => $testId,
            'testJson' => $testJson,
            // 'user' => $user,
        ]);
    }

    public function submit(Request $request)
    {
        \Log::debug($request);
    }
}
