<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WordController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;

Auth::routes((['register' => false, 'reset' => false, 'verify' => false, 'confirm' => false]));

Route::get('/', [WordController::class, 'index'])->name('words.index');
Route::get('/words/create', [WordController::class, 'create'])->name('words.create');
Route::get('/words/{word}', [WordController::class, 'show'])->name('words.show');
Route::get('/words/edit/{id}', [WordController::class, 'edit'])->name('words.edit');

Route::get('/random', [WordController::class, 'getRandomWord'])->name('words.random');
Route::get('/search/{type}/{searchString?}', [SearchController::class, 'search'])->name('words.search');

Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
Route::get('/tags/create', [TagController::class, 'create'])->name('tags.create');
Route::get('/tags/{tag}', [TagController::class, 'show'])->name('tags.show');
Route::get('/tags/edit/{id}', [TagController::class, 'edit'])->name('tags.edit');