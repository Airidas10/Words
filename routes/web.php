<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WordController;
use App\Http\Controllers\SearchController;

Route::get('/', [WordController::class, 'index'])->name('words.index');
Route::get('/words/create', [WordController::class, 'create'])->name('words.create');
Route::get('/words/{word}', [WordController::class, 'show'])->name('words.show');
Route::get('/words/edit/{id}', [WordController::class, 'edit'])->name('words.edit');

Route::get('/random', [WordController::class, 'getRandomWord'])->name('words.random');
Route::get('/search/{type}/{searchString?}', [SearchController::class, 'search'])->name('words.search');