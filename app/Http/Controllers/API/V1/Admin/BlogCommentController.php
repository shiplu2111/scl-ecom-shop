<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use Illuminate\Http\Request;

/**
 * @group Admin
 * @subgroup Blog Management
 */
class BlogCommentController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogComment::with(['user', 'post', 'parent']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('blog_post_id')) {
            $query->where('blog_post_id', $request->blog_post_id);
        }

        return response()->json([
            'status' => true,
            'message' => 'Comments fetched',
            'data' => $query->latest()->paginate($request->get('limit', 15))
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,spam'
        ]);

        $comment = BlogComment::findOrFail($id);
        $comment->update(['status' => $request->status]);

        return response()->json([
            'status' => true,
            'message' => 'Comment status updated successfully',
            'data' => $comment
        ]);
    }

    public function destroy($id)
    {
        BlogComment::findOrFail($id)->delete();
        return response()->json([
            'status' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }
}
