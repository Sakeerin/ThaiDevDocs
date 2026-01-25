<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\CommentVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\CommonMark\CommonMarkConverter;

class CommentController extends Controller
{
    /**
     * Get comments for an article.
     */
    public function index(Request $request, string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)->published()->first();

        if (!$article) {
            return $this->notFound('Article not found.');
        }

        if (!$article->allow_comments) {
            return $this->error('Comments are disabled for this article.', 'COMMENTS_DISABLED', 403);
        }

        $comments = $article->comments()
            ->approved()
            ->with([
                'user:id,name,avatar',
                'replies' => function ($q) {
                    $q->approved()
                        ->with('user:id,name,avatar')
                        ->orderBy('created_at');
                },
            ])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        $userId = $request->user()?->id;

        return $this->successWithPagination($comments->through(fn ($comment) => $this->formatComment($comment, $userId)));
    }

    /**
     * Add a comment.
     */
    public function store(Request $request, string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)->published()->first();

        if (!$article) {
            return $this->notFound('Article not found.');
        }

        if (!$article->allow_comments) {
            return $this->error('Comments are disabled for this article.', 'COMMENTS_DISABLED', 403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:5000'],
            'parent_id' => ['sometimes', 'nullable', 'exists:comments,id'],
        ]);

        // Validate parent belongs to same article
        if (isset($validated['parent_id'])) {
            $parent = Comment::find($validated['parent_id']);
            if (!$parent || $parent->article_id !== $article->id) {
                return $this->error('Invalid parent comment.', 'INVALID_PARENT', 400);
            }
        }

        // Convert markdown to HTML
        $converter = new CommonMarkConverter(['html_input' => 'strip']);
        $contentHtml = $converter->convert($validated['content'])->getContent();

        $comment = Comment::create([
            'article_id' => $article->id,
            'user_id' => $request->user()->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'content_html' => $contentHtml,
        ]);

        $comment->load('user:id,name,avatar');

        return $this->success([
            'comment' => $this->formatComment($comment, $request->user()->id),
        ], 'Comment posted successfully.', 201);
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $comment = Comment::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$comment) {
            return $this->notFound('Comment not found.');
        }

        // Only allow editing within 15 minutes
        if ($comment->created_at->diffInMinutes(now()) > 15) {
            return $this->error('Comments can only be edited within 15 minutes.', 'EDIT_TIMEOUT', 403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:5000'],
        ]);

        $converter = new CommonMarkConverter(['html_input' => 'strip']);
        $contentHtml = $converter->convert($validated['content'])->getContent();

        $comment->update([
            'content' => $validated['content'],
            'content_html' => $contentHtml,
        ]);

        return $this->success([
            'comment' => $this->formatComment($comment->fresh(), $request->user()->id),
        ], 'Comment updated.');
    }

    /**
     * Delete a comment.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $comment = Comment::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$comment) {
            return $this->notFound('Comment not found.');
        }

        $comment->delete();

        return $this->success(null, 'Comment deleted.');
    }

    /**
     * Vote on a comment.
     */
    public function vote(Request $request, int $id): JsonResponse
    {
        $comment = Comment::approved()->find($id);

        if (!$comment) {
            return $this->notFound('Comment not found.');
        }

        $validated = $request->validate([
            'vote_type' => ['required', 'in:up,down'],
        ]);

        $existingVote = CommentVote::where('user_id', $request->user()->id)
            ->where('comment_id', $id)
            ->first();

        if ($existingVote) {
            if ($existingVote->vote_type === $validated['vote_type']) {
                // Remove vote
                $existingVote->delete();
                if ($validated['vote_type'] === 'up') {
                    $comment->decrement('upvote_count');
                }
                return $this->success(['upvote_count' => $comment->fresh()->upvote_count], 'Vote removed.');
            } else {
                // Change vote
                $existingVote->update(['vote_type' => $validated['vote_type']]);
                if ($validated['vote_type'] === 'up') {
                    $comment->increment('upvote_count');
                } else {
                    $comment->decrement('upvote_count');
                }
            }
        } else {
            // New vote
            CommentVote::create([
                'user_id' => $request->user()->id,
                'comment_id' => $id,
                'vote_type' => $validated['vote_type'],
            ]);
            if ($validated['vote_type'] === 'up') {
                $comment->increment('upvote_count');
            }
        }

        return $this->success([
            'upvote_count' => $comment->fresh()->upvote_count,
            'user_vote' => $validated['vote_type'],
        ], 'Vote recorded.');
    }

    /**
     * Format comment for response.
     */
    protected function formatComment(Comment $comment, ?int $userId = null): array
    {
        $data = [
            'id' => $comment->id,
            'content' => $comment->content,
            'content_html' => $comment->content_html,
            'is_pinned' => $comment->is_pinned,
            'upvote_count' => $comment->upvote_count,
            'created_at' => $comment->created_at?->toISOString(),
            'updated_at' => $comment->updated_at?->toISOString(),
            'is_author' => $userId && $comment->user_id === $userId,
            'user' => $comment->user ? [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'avatar' => $comment->user->avatar,
            ] : null,
        ];

        if ($userId) {
            $vote = $comment->votes()->where('user_id', $userId)->first();
            $data['user_vote'] = $vote?->vote_type;
        }

        if ($comment->relationLoaded('replies') && $comment->replies->isNotEmpty()) {
            $data['replies'] = $comment->replies->map(fn ($reply) => $this->formatComment($reply, $userId));
        }

        return $data;
    }
}
