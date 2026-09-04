<?php

use App\Http\Controllers\ClassroomController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/classroom');

Route::get('/classroom', [ClassroomController::class, 'show'])
    ->name('classroom.show');

Route::post('/classroom/join', [ClassroomController::class, 'join'])
    ->name('classroom.join');
