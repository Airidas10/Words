<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WordController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TagController;

Route::post('/words/create', [WordController::class, 'store'])->name('words.store'); 
Route::match(['put', 'patch'], '/words/update/{id}', [WordController::class, 'update'])->name('words.update');
Route::delete('/words/destroy/{id}', [WordController::class, 'destroy'])->name('words.destroy');

Route::post('/tags/create', [TagController::class, 'store'])->name('tags.store'); 
Route::match(['put', 'patch'], '/tags/update/{id}', [TagController::class, 'update'])->name('tags.update');
Route::delete('/tags/destroy/{id}', [TagController::class, 'destroy'])->name('tags.destroy');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/tests/submit', [TestController::class, 'submit'])->name('tests.submit');
});
