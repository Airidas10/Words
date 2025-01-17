<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WordController;

Route::post('/words/create', [WordController::class, 'store'])->name('words.store'); 
Route::match(['put', 'patch'], '/words/update/{id}', [WordController::class, 'update'])->name('words.update');
Route::delete('/words/destroy/{id}', [WordController::class, 'destroy'])->name('words.destroy');
