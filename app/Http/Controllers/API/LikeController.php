<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    // Toggle Like Post
    public function toggleLike($postID)
    {
        $post = Post::findOrFail($postID);
        // Check If User Already Like
        $existingLike = $post->likes()->where('user_id', Auth::id())->first();

        if ($existingLike) {
            // Unlike
            $existingLike->delete();

            return response()->json([
                'status'        => true,
                'message'       => 'Post Unliked Successfully.',
                'liked'         => false,
                'total_likes'   => $post->likes()->count()
            ]);
        } else {
            // Like
            $like = $post->likes()->create([
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status'        => true,
                'message'       => 'Post Liked Successfully.',
                'liked'         => true,
                'total_likes'   => $post->likes()->count()
            ]);
        }
    }

    // All Likes Post
    public function getPostLikes($postID)
    {
        $post = Post::with('likes.user')->findOrFail($postID);

        $likes = $post->likes()
        ->with('user')
        ->paginate(request('per_page', 10));

        return response()->json([
            'status'        => true,
            'message'       => 'Post Likes Fetched Successfully.',
            'total_likes'   => $post->likes->count(),
            'likes'         => $likes
        ]);
    }

    // Check if the logged-in user has liked a specific post
    public function hasLiked($postID)
    {
        $post = Post::findOrFail($postID);

        $hasLiked = $post->likes()->where('user_id', Auth::id())->exists();

        return response()->json([
            'status' => true,
            'liked'  => $hasLiked
        ]);
    }
}
