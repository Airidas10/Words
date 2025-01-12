<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WordController;

Route::post('/words/create', [WordController::class, 'store'])->name('words.store')->middleware('auth:sanctum');
Route::post('/words/update/{id}', [WordController::class, 'update'])->name('words.update')->middleware('auth:sanctum');
Route::post('/words/destroy/{id}', [WordController::class, 'destroy'])->name('words.destroy')->middleware('auth:sanctum');
