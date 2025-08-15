<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

// Google 
Route::get('/auth/google', [AuthController::class, 'redirectToProvider']);
Route::get('/auth/google/callback', [AuthController::class, 'handleProviderCallback']);

Route::get('/', function () {
    return view('login');
});

Route::get('/register', function () {
    return view('register');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/posts', function () {
    return view('posts');
})->name('posts');

Route::get('/posts/{id}', function () {
    return view('show-post');
})->name('posts.show');

Route::get('/my-posts', function () {
    return view('my-posts');
})->name('my.posts');

Route::get('/profile/{id}', function ($id) {
    return view('profile', ['id' => $id]);
})->name('profile');

