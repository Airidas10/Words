<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\WordController;

Route::get('/', [HomeController::class, 'index'])->name('words.index');
Route::get('/words/{word}', [WordController::class, 'show'])->name('words.show');