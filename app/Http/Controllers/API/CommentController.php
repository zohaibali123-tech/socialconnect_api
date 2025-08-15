<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // ShoW Comments
    public function index($postId)
    {
        $comments = Comment::with('user')
            ->where('post_id', $postId)
            ->latest()
            ->paginate(request('per_page', 5));

        return response()->json($comments);
    }

    // Add Comment
    public function store(Request $request, $postID)
    {
        $request->validate([
            'comment_text' => 'required|string|max:1000'
        ]);

        $post = Post::findOrFail($postID);

        $comment = $post->comments()->create([
            'user_id'       => Auth::id(),
            'comment_text'  => $request->comment_text
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Comment Added Successfully.',
            'comment'   => $comment
        ]);
    }

    // Update Comment
    public function update(Request $request, $id)
    {
        $request->validate([
            'comment_text' => 'required|string|max:1000'
        ]);

        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'status'    => false,
                'message'   => 'Unauthorized'
            ], 403);
        }

        $comment->update([
            'comment_text' => $request->comment_text
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Comment Updated Successfully',
            'comment'   => $comment
        ], 200);
    }

    // Delete Comment
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'status'    => false,
                'message'   => 'Unauthorized'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Comment Deleted Successfully.'
        ]);
    }
}
