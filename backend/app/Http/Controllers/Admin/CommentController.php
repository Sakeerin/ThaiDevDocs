<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * List all comments.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Comment::with([
            'user:id,name,email,avatar',
            'article:id,title,slug',
        ])->withTrashed();

        // Filter by status
        if ($request->input('status') === 'pending') {
            $query->where('is_approved', false);
        } elseif ($request->input('status') === 'approved') {
            $query->where('is_approved', true);
        }

        // Filter by article
        if ($articleId = $request->input('article_id')) {
            $query->where('article_id', $articleId);
        }

        $comments = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $comments->items(),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
                'last_page' => $comments->lastPage(),
            ],
        ]);
    }

    /**
     * Get pending comments.
     */
    public function pending(Request $request): JsonResponse
    {
        $comments = Comment::where('is_approved', false)
            ->with([
                'user:id,name,email,avatar',
                'article:id,title,slug',
            ])
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $comments->items(),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
                'last_page' => $comments->lastPage(),
            ],
        ]);
    }

    /**
     * Approve a comment.
     */
    public function approve(int $id): JsonResponse
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Comment not found.'],
            ], 404);
        }

        $comment->update(['is_approved' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Comment approved successfully.',
        ]);
    }

    /**
     * Reject a comment.
     */
    public function reject(int $id): JsonResponse
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Comment not found.'],
            ], 404);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment rejected and deleted.',
        ]);
    }
}
