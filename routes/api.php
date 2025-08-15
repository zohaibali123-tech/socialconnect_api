<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LikeController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CommentController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'status' => true,
        'user' => $request->user()
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Post API Routes
    Route::apiResource('posts', PostController::class);
    Route::get('/my-posts', [PostController::class, 'myPosts']);
    // Comment API Routes
    Route::post('/posts/{postId}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    Route::get('/posts/{id}/comments', [CommentController::class, 'index']);
    // Likes API Routes
    Route::post('/posts/{postId}/like', [LikeController::class, 'toggleLike']);
    Route::get('/posts/{postID}/likes', [LikeController::class, 'getPostLikes']);
    Route::get('/posts/{postID}/has-liked', [LikeController::class, 'hasLiked']);
    // Profile routes
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/profile/update', [UserController::class, 'updateProfile']);
    Route::post('/profile/update-picture', [UserController::class, 'updateProfilePicture']);
    // Search Posts
    Route::get('/search/posts', [PostController::class, 'search']);
});