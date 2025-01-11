<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;

use App\Models\Word;

class HomeController extends Controller
{
    public function index()
    {
        $words = Word::with('tags')->get();

        return Inertia::render('Homepage', [
            'words' => $words
        ]);
    }
}
