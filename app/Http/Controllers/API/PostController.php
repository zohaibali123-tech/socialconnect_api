<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    // Show All Posts
    public function index()
    {
        $posts = Post::with('user')->withCount('likes', 'comments')->latest()->paginate(6);

        // Add liked_by_user field for each post
        $posts->getCollection()->transform(function ($post) {
            $post->liked_by_user = $post->likes()->where('user_id', Auth::id())->exists();
            return $post;
        });

        return response()->json([
            'status'    => true,
            'message'   => 'Posts Fetched Successfully.',
            'post'      => $posts
        ]);
    }

    // Add New Post
    public function store(Request $request)
    {
        $Validator = Validator::make($request->all(), [
            'title'         => 'required|string|max:255',
            'content'       => 'required|string',
            'post_image'    => 'nullable|image|max:2048'
        ]);

        if ($Validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'Validation Error.',
                'errors'    => $Validator->errors()
            ], 422);
        }

        $post_image = null;
        if ($request->hasFile('post_image')) {
            $post_image = $request->file('post_image')->store('post_images', 'public');
        }

        $post = Post::create([
            'user_id'       => Auth::id(),
            'title'         => $request->title,
            'content'       => $request->content,
            'post_image'    => $post_image
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Post Created Successfully.',
            'post'      => $post
        ], 201);
    }

    // Show Single Post
    public function show($id)
    {
        $post = Post::with('user', 'comments.user', 'likes.user')->find($id);

        if (!$post) {
            return response()->json([
                'status'    => false,
                'message'   => 'Post Not Found.'
            ], 404);
        }

        if ($post->post_image) {
            $post->post_image = asset('storage/' . $post->post_image);
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Your Single Post.',
            'post'      => $post
        ]);
    }

    // Update Post
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post || $post->user_id !== Auth::id()) {
            return response()->json([
                'status'    => false,
                'message'   => 'Unauthorized or Post Not Found.'
            ], 403);
        }

        $Validator = Validator::make($request->all(), [
            'title'         => 'sometimes|string|max:255',
            'content'       => 'sometimes|string',
            'post_image'    => 'nullable|image|max:2048'
        ]);

        if ($Validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'Validation Failed.',
                'errors'    => $Validator->errors()
            ], 422);
        }

        if ($request->hasFile('post_image')) {
            if ($post->post_image && Storage::disk('public')->exists($post->post_image)) {
                Storage::disk('public')->delete($post->post_image);
            }
            $post->post_image = $request->file('post_image')->store('post_images', 'public');
        }

        $post->title = $request->title ?? $post->title;
        $post->content = $request->content ?? $post->content;
        $post->save();

        return response()->json([
            'status'    => true,
            'message'   => 'Post Updated Successfully.',
            'post'      => $post
        ], 201);
    }

    // Delete Post
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post || $post->user_id !== Auth::id()) {
            return response()->json([
                'status'    => false,
                'message'   => 'Unauthorized or Post Not Found.',
            ], 403);
        }

        if ($post->post_image && Storage::disk('public')->exists($post->post_image)) {
            Storage::disk('public')->delete($post->post_image);
        }

        $post->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Post Deleted Successfully.',
        ]);
    }

    // User All Post
    public function myPosts(Request $request)
    {
        $posts = Post::where('user_id', Auth::id())
            ->with('user')
            ->withCount('likes', 'comments')
            ->latest()
            ->paginate($request->get('per_page', 6));

        // Add liked_by_user field
        $posts->getCollection()->transform(function ($post) {
            $post->liked_by_user = $post->likes()->where('user_id', Auth::id())->exists();
            if ($post->post_image) {
                $post->post_image = asset('storage/' . $post->post_image);
            }
            return $post;
        });

        return response()->json([
            'status' => true,
            'message' => 'My Posts Fetched Successfully',
            'posts' => $posts
        ]);
    }

    // Search Post
    public function search(Request $request)
    {
        $query = $request->get('query', '');

        $posts = Post::where('title', 'like', "%{$query}%")
            ->with('user')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => true,
            'posts' => $posts
        ]);
    }
}
