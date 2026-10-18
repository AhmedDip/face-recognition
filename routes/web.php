<?php

use App\Http\Controllers\FaceController;
use App\Http\Controllers\PersonController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('people.index'));

Route::resource('people', PersonController::class)->except(['show', 'edit', 'update']);
Route::get('/api/descriptors', [PersonController::class, 'descriptors'])->name('people.descriptors');

Route::get('/recognize', [FaceController::class, 'recognize'])->name('recognize');
