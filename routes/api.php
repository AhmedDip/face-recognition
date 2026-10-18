<?php

use App\Http\Controllers\FaceController;
use Illuminate\Support\Facades\Route;

Route::post('/recognize', [FaceController::class, 'match'])
    ->middleware('throttle:60,1')
    ->name('api.recognize');
