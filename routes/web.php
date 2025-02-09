<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WordController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TestController;

Auth::routes((['register' => false, 'reset' => false, 'verify' => false, 'confirm' => false]));

Route::middleware(['auth'])->group(function () {
    Route::get('/words/create', [WordController::class, 'create'])->name('words.create');
    Route::get('/words/edit/{id}', [WordController::class, 'edit'])->name('words.edit');

    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::get('/tags/create', [TagController::class, 'create'])->name('tags.create');
    Route::get('/tags/{tag}', [TagController::class, 'show'])->name('tags.show');
    Route::get('/tags/edit/{id}', [TagController::class, 'edit'])->name('tags.edit');

    Route::get('/daily-dose', [TestController::class, 'index'])->name('tests.index');
    Route::get('/my-tests', [TestController::class, 'myTests'])->name('tests.tests');
    Route::get('/runs/{id}', [TestController::class, 'show'])->name('tests.show');
});

Route::get('/', [WordController::class, 'index'])->name('words.index');
Route::get('/words/{word}', [WordController::class, 'show'])->name('words.show');
Route::get('/random', [WordController::class, 'getRandomWord'])->name('words.random');
Route::get('/search/{type}/{searchString?}', [SearchController::class, 'search'])->name('words.search');