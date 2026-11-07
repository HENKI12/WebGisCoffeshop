<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::get('/', fn () => auth()->check() ? redirect()->route('webgis') : view('public'));
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/webgis', fn () => view('app'))->middleware('auth')->name('webgis');
Route::get('/publik', fn () => view('public'))->name('public.map');
