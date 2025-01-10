<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $words = ['Foo', 'Bar', 'Baz'];

        return Inertia::render('Homepage', [
            'words' => $words
        ]);
    }
}
