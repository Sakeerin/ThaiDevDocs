<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EditSuggestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\CommonMark\CommonMarkConverter;

class EditSuggestionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EditSuggestion::with([
            'user:id,name,email,avatar',
            'article:id,title,slug',
            'reviewer:id,name',
        ])->orderByDesc('created_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $suggestions = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $suggestions->items(),
            'meta' => [
                'current_page' => $suggestions->currentPage(),
                'per_page' => $suggestions->perPage(),
                'total' => $suggestions->total(),
                'last_page' => $suggestions->lastPage(),
            ],
        ]);
    }

    public function pending(): JsonResponse
    {
        $suggestions = EditSuggestion::pending()
            ->with([
                'user:id,name,email,avatar',
                'article:id,title,slug',
            ])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $suggestion = EditSuggestion::with([
            'user:id,name,email,avatar',
            'article:id,title,slug,content',
            'reviewer:id,name',
        ])->find($id);

        if (!$suggestion) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Edit suggestion not found.'],
            ], 404);
        }

        return response()->json(['success' => true, 'data' => $suggestion]);
    }

    public function review(Request $request, int $id): JsonResponse
    {
        $suggestion = EditSuggestion::with('article')->find($id);

        if (!$suggestion) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Edit suggestion not found.'],
            ], 404);
        }

        if ($suggestion->status !== 'pending') {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'ALREADY_REVIEWED', 'message' => 'This suggestion has already been reviewed.'],
            ], 400);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'review_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'apply_to_article' => ['sometimes', 'boolean'],
        ]);

        $suggestion->update([
            'status' => $validated['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        if ($validated['status'] === 'approved' && ($validated['apply_to_article'] ?? true)) {
            $this->applySuggestionToArticle($suggestion);
        }

        return response()->json([
            'success' => true,
            'data' => $suggestion->fresh(['user:id,name', 'article:id,title', 'reviewer:id,name']),
            'message' => 'Edit suggestion reviewed successfully.',
        ]);
    }

    protected function applySuggestionToArticle(EditSuggestion $suggestion): void
    {
        $article = $suggestion->article;

        if (!$article) {
            return;
        }

        $content = $article->content;

        if (str_contains($content, $suggestion->original_content)) {
            $content = str_replace(
                $suggestion->original_content,
                $suggestion->suggested_content,
                $content
            );
        } else {
            $content = $suggestion->suggested_content;
        }

        $converter = new CommonMarkConverter(['html_input' => 'strip']);
        $contentHtml = $converter->convert($content)->getContent();

        $article->update([
            'content' => $content,
            'content_html' => $contentHtml,
            'reviewer_id' => $suggestion->reviewed_by,
            'last_reviewed_at' => now(),
        ]);
    }
}
