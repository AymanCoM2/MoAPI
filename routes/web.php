<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('homeTwo');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


