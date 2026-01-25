<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    /**
     * List all topics.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Topic::with('category:id,name,slug')
            ->withCount('articles');

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $topics = $query->orderBy('category_id')->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $topics,
        ]);
    }

    /**
     * Create a new topic.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string'],
        ]);

        // Get next sort order
        $maxOrder = Topic::where('category_id', $validated['category_id'])
            ->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;

        $topic = Topic::create($validated);

        return response()->json([
            'success' => true,
            'data' => $topic->load('category'),
            'message' => 'Topic created successfully.',
        ], 201);
    }

    /**
     * Get topic details.
     */
    public function show(int $id): JsonResponse
    {
        $topic = Topic::with(['category', 'articles'])->find($id);

        if (!$topic) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Topic not found.'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $topic,
        ]);
    }

    /**
     * Update a topic.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $topic = Topic::find($id);

        if (!$topic) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Topic not found.'],
            ], 404);
        }

        $validated = $request->validate([
            'category_id' => ['sometimes', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'name_en' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string'],
        ]);

        $topic->update($validated);

        return response()->json([
            'success' => true,
            'data' => $topic->fresh('category'),
            'message' => 'Topic updated successfully.',
        ]);
    }

    /**
     * Delete a topic.
     */
    public function destroy(int $id): JsonResponse
    {
        $topic = Topic::withCount('articles')->find($id);

        if (!$topic) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Topic not found.'],
            ], 404);
        }

        if ($topic->articles_count > 0) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'HAS_ARTICLES',
                    'message' => 'Cannot delete topic with articles. Move or delete articles first.',
                ],
            ], 400);
        }

        $topic->delete();

        return response()->json([
            'success' => true,
            'message' => 'Topic deleted successfully.',
        ]);
    }

    /**
     * Reorder topics.
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:topics,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['items'] as $item) {
            Topic::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Topics reordered successfully.',
        ]);
    }
}
