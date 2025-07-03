<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

Route::redirect('/', '/admin/login');

Route::post('/image-upload', function(Request $request) {
    $request->validate([
        'file' => 'required|image|max:2048', // max 2MB
    ]);

    $path = $request->file('file')->store('public/uploads');

    return response()->json([
        'location' => Storage::url($path), // URL to the uploaded image
    ]);
})->name('image.upload');
