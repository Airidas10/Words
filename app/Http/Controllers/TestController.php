<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;
use Auth;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;

use App\Models\Word;
use App\Models\Translation;
use App\Models\Test;

class TestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $lastTest = $user->tests()->latest()->first();
        $questionsPerTest = config('words.questions_per_test');

        // If no last test found, or last one is not from today and has a score, create new test...
        if(!$lastTest || ($lastTest && $lastTest->score !== null)){
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
        $request->validate([
            'testId' => 'required|exists:tests,id',
            'testData' => 'present|array',
            'testData.*.id' => 'required|exists:words,id',
            'testData.*.type' => 'required|in:w,t',
            'testData.*.question' => 'required',
            'testData.*.answer' => 'present|nullable|string|max:191',
        ]);

        $response = ['status' => 'error', 'msg' => 'Something went wrong!', 'data' => null];

        DB::transaction(function() use ($request, &$response){
            $user = Auth::user();
            if(!$user){
                abort(403, 'No logged in user found!');
            }
            $testRun = $user->tests()->where('id', $request->testId)->first();
            if(!$testRun){
                return ['status' => 'error', 'msg' => 'No such test run found!', 'data' => null];
            }

            $questionsAndAnswers = json_decode($testRun->questions_and_answers, true);

            $answers = collect();
            $score = 0;
            foreach($request->testData as $key => $datum){
                $correct = false;
                $correctAnswer = null;
                $personAnswer = $datum['answer'];
                $wordAsked = Word::find($datum['id']);
                $answer = mb_strtolower($personAnswer);
                $questionsAndAnswers[$key]['answer'] = $personAnswer; 
                switch ($datum['type']){
                    case 'w':
                        $translations = $wordAsked->translations->pluck('translation');
                        // $correctAnswer = $translations[0];
                        foreach($translations as $index => $translation){
                            $translation = mb_strtolower($translation);
                            if($index == 0){
                                $correctAnswer = $translation;
                            } else{
                                $correctAnswer .= ' | ' . $translation;
                            }
                            if($answer == $translation){
                                $correct = true;
                                $score++;
                                // break;
                            }
                        }

                        break;
                    case 't':
                        $correctAnswer = mb_strtolower($wordAsked->word);
                        if($answer == $correctAnswer){
                            $correct = true;
                            $score++;
                        }
                        break;
                    default:
                        \Log::error("Undefined type " . $datum['type']);
                        break;
                }

                $answerData = ['id' => $wordAsked->id, 'correct' => $correct, 'correctAnswer' => $correctAnswer];
                $answers->push($answerData);
            }

            $data['score'] = $score;
            $data['answers'] = $answers;

            $testRun->questions_and_answers = json_encode($questionsAndAnswers);
            $testRun->score = $score;
            $testRun->save();

            $response = ['status' => 'success', 'msg' => 'Test was submitted!', 'data' => $data];
        }, 5);

        return $response;
    }
}
