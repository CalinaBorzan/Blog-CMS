<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ReactionController;

Route::redirect('/', '/admin/login');
Route::post('/reaction/{post}/{type}', [ReactionController::class, 'store'])->name('filament.reaction')->middleware('auth');
