<?php

use App\Http\Controllers\WallController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/m/{wall}', [WallController::class, 'show'])->name('walls.show');
Route::get('/m/{wall}/gerer', [WallController::class, 'manage'])->name('walls.manage');
