<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearningPath;
use App\Models\LearningPathItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LearningPathController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LearningPath::with('author:id,name,avatar')
            ->withCount('items');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        $paths = $query->orderByDesc('updated_at')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $paths->items(),
            'meta' => [
                'current_page' => $paths->currentPage(),
                'per_page' => $paths->perPage(),
                'total' => $paths->total(),
                'last_page' => $paths->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'difficulty' => ['sometimes', 'in:beginner,intermediate,advanced'],
            'estimated_hours' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'thumbnail' => ['sometimes', 'nullable', 'string', 'max:500'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
            'items' => ['sometimes', 'array'],
            'items.*.article_id' => ['required_with:items', 'exists:articles,id'],
            'items.*.sort_order' => ['required_with:items', 'integer', 'min:0'],
            'items.*.is_required' => ['sometimes', 'boolean'],
            'items.*.notes' => ['sometimes', 'nullable', 'string'],
        ]);

        $path = DB::transaction(function () use ($validated, $request) {
            $path = LearningPath::create([
                'author_id' => $request->user()->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'difficulty' => $validated['difficulty'] ?? 'beginner',
                'estimated_hours' => $validated['estimated_hours'] ?? null,
                'thumbnail' => $validated['thumbnail'] ?? null,
                'is_featured' => $validated['is_featured'] ?? false,
                'is_published' => $validated['is_published'] ?? false,
            ]);

            if (!empty($validated['items'])) {
                $this->syncItems($path, $validated['items']);
            }

            return $path;
        });

        return response()->json([
            'success' => true,
            'data' => $path->load(['author:id,name,avatar', 'items.article:id,title,slug']),
            'message' => 'Learning path created successfully.',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $path = LearningPath::with([
            'author:id,name,avatar',
            'items' => fn ($q) => $q->orderBy('sort_order'),
            'items.article:id,title,slug,reading_time,difficulty',
        ])->find($id);

        if (!$path) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Learning path not found.'],
            ], 404);
        }

        return response()->json(['success' => true, 'data' => $path]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $path = LearningPath::find($id);

        if (!$path) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Learning path not found.'],
            ], 404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'difficulty' => ['sometimes', 'in:beginner,intermediate,advanced'],
            'estimated_hours' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'thumbnail' => ['sometimes', 'nullable', 'string', 'max:500'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
            'items' => ['sometimes', 'array'],
            'items.*.article_id' => ['required_with:items', 'exists:articles,id'],
            'items.*.sort_order' => ['required_with:items', 'integer', 'min:0'],
            'items.*.is_required' => ['sometimes', 'boolean'],
            'items.*.notes' => ['sometimes', 'nullable', 'string'],
        ]);

        DB::transaction(function () use ($path, $validated) {
            $items = $validated['items'] ?? null;
            unset($validated['items']);

            if (!empty($validated)) {
                $path->update($validated);
            }

            if ($items !== null) {
                $this->syncItems($path, $items);
            }
        });

        return response()->json([
            'success' => true,
            'data' => $path->fresh(['author:id,name,avatar', 'items.article:id,title,slug']),
            'message' => 'Learning path updated successfully.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $path = LearningPath::find($id);

        if (!$path) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Learning path not found.'],
            ], 404);
        }

        $path->delete();

        return response()->json([
            'success' => true,
            'message' => 'Learning path deleted successfully.',
        ]);
    }

    public function publish(int $id): JsonResponse
    {
        $path = LearningPath::withCount('items')->find($id);

        if (!$path) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Learning path not found.'],
            ], 404);
        }

        if ($path->items_count === 0) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NO_ITEMS', 'message' => 'Add at least one lesson before publishing.'],
            ], 400);
        }

        $path->update(['is_published' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Learning path published successfully.',
        ]);
    }

    protected function syncItems(LearningPath $path, array $items): void
    {
        $path->items()->delete();

        foreach ($items as $item) {
            LearningPathItem::create([
                'learning_path_id' => $path->id,
                'article_id' => $item['article_id'],
                'sort_order' => $item['sort_order'],
                'is_required' => $item['is_required'] ?? true,
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }
}
