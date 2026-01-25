<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningPath;
use App\Models\UserLearningProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearningPathController extends Controller
{
    /**
     * List learning paths.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LearningPath::published()
            ->with('author:id,name,avatar')
            ->withCount('items');

        if ($request->boolean('featured')) {
            $query->featured();
        }

        if ($difficulty = $request->input('difficulty')) {
            $query->where('difficulty', $difficulty);
        }

        $paths = $query->orderByDesc('enrollment_count')
            ->paginate($request->input('per_page', 12));

        return $this->successWithPagination($paths->through(fn ($path) => [
            'id' => $path->id,
            'title' => $path->title,
            'slug' => $path->slug,
            'description' => $path->description,
            'difficulty' => $path->difficulty,
            'estimated_hours' => $path->estimated_hours,
            'thumbnail' => $path->thumbnail,
            'is_featured' => $path->is_featured,
            'enrollment_count' => $path->enrollment_count,
            'completion_count' => $path->completion_count,
            'average_rating' => $path->average_rating,
            'items_count' => $path->items_count,
            'author' => $path->author ? [
                'id' => $path->author->id,
                'name' => $path->author->name,
                'avatar' => $path->author->avatar,
            ] : null,
        ]));
    }

    /**
     * Get learning path details.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $path = LearningPath::where('slug', $slug)
            ->published()
            ->with([
                'author:id,name,avatar,bio',
                'items.article:id,title,slug,summary,difficulty,reading_time',
            ])
            ->first();

        if (!$path) {
            return $this->notFound('Learning path not found.');
        }

        $userProgress = null;
        if ($request->user()) {
            $progress = UserLearningProgress::where('user_id', $request->user()->id)
                ->where('learning_path_id', $path->id)
                ->first();

            if ($progress) {
                $userProgress = [
                    'started_at' => $progress->started_at?->toISOString(),
                    'completed_at' => $progress->completed_at?->toISOString(),
                    'progress_percentage' => $progress->progress_percentage,
                    'current_item_id' => $progress->current_item_id,
                ];
            }
        }

        return $this->success([
            'path' => [
                'id' => $path->id,
                'title' => $path->title,
                'slug' => $path->slug,
                'description' => $path->description,
                'difficulty' => $path->difficulty,
                'estimated_hours' => $path->estimated_hours,
                'thumbnail' => $path->thumbnail,
                'enrollment_count' => $path->enrollment_count,
                'completion_count' => $path->completion_count,
                'average_rating' => $path->average_rating,
                'author' => $path->author ? [
                    'id' => $path->author->id,
                    'name' => $path->author->name,
                    'avatar' => $path->author->avatar,
                    'bio' => $path->author->bio,
                ] : null,
                'items' => $path->items->map(fn ($item) => [
                    'id' => $item->id,
                    'sort_order' => $item->sort_order,
                    'is_required' => $item->is_required,
                    'notes' => $item->notes,
                    'article' => $item->article ? [
                        'id' => $item->article->id,
                        'title' => $item->article->title,
                        'slug' => $item->article->slug,
                        'summary' => $item->article->summary,
                        'difficulty' => $item->article->difficulty,
                        'reading_time' => $item->article->reading_time,
                    ] : null,
                ]),
            ],
            'user_progress' => $userProgress,
        ]);
    }

    /**
     * Enroll in a learning path.
     */
    public function enroll(Request $request, string $slug): JsonResponse
    {
        $path = LearningPath::where('slug', $slug)->published()->first();

        if (!$path) {
            return $this->notFound('Learning path not found.');
        }

        $progress = UserLearningProgress::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'learning_path_id' => $path->id,
            ],
            [
                'started_at' => now(),
                'current_item_id' => $path->items()->orderBy('sort_order')->first()?->id,
                'progress_percentage' => 0,
            ]
        );

        if ($progress->wasRecentlyCreated) {
            $path->increment('enrollment_count');
        }

        return $this->success([
            'enrolled' => true,
            'progress' => [
                'started_at' => $progress->started_at?->toISOString(),
                'progress_percentage' => $progress->progress_percentage,
            ],
        ], 'Successfully enrolled in learning path.');
    }

    /**
     * Update learning progress.
     */
    public function updateProgress(Request $request, string $slug): JsonResponse
    {
        $path = LearningPath::where('slug', $slug)->first();

        if (!$path) {
            return $this->notFound('Learning path not found.');
        }

        $progress = UserLearningProgress::where('user_id', $request->user()->id)
            ->where('learning_path_id', $path->id)
            ->first();

        if (!$progress) {
            return $this->error('You are not enrolled in this learning path.', 'NOT_ENROLLED', 400);
        }

        $validated = $request->validate([
            'completed_item_id' => ['sometimes', 'exists:learning_path_items,id'],
            'current_item_id' => ['sometimes', 'exists:learning_path_items,id'],
        ]);

        if (isset($validated['completed_item_id'])) {
            // Record completed item
            \DB::table('user_completed_items')->insertOrIgnore([
                'user_id' => $request->user()->id,
                'learning_path_item_id' => $validated['completed_item_id'],
                'completed_at' => now(),
            ]);
        }

        // Calculate progress percentage
        $totalItems = $path->items()->count();
        $completedItems = \DB::table('user_completed_items')
            ->where('user_id', $request->user()->id)
            ->whereIn('learning_path_item_id', $path->items()->pluck('id'))
            ->count();

        $progressPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

        $progress->update([
            'current_item_id' => $validated['current_item_id'] ?? $progress->current_item_id,
            'progress_percentage' => $progressPercentage,
            'completed_at' => $progressPercentage >= 100 ? now() : null,
        ]);

        // Update path completion count
        if ($progressPercentage >= 100 && !$progress->getOriginal('completed_at')) {
            $path->increment('completion_count');
        }

        return $this->success([
            'progress_percentage' => $progressPercentage,
            'is_completed' => $progressPercentage >= 100,
        ], 'Progress updated.');
    }

    /**
     * Get user's enrolled learning paths.
     */
    public function myLearning(Request $request): JsonResponse
    {
        $progress = $request->user()->learningProgress()
            ->with([
                'learningPath:id,title,slug,thumbnail,difficulty,estimated_hours',
                'learningPath.items:id,learning_path_id',
            ])
            ->orderByDesc('started_at')
            ->get();

        return $this->success([
            'learning' => $progress->map(fn ($p) => [
                'path' => $p->learningPath ? [
                    'id' => $p->learningPath->id,
                    'title' => $p->learningPath->title,
                    'slug' => $p->learningPath->slug,
                    'thumbnail' => $p->learningPath->thumbnail,
                    'difficulty' => $p->learningPath->difficulty,
                    'estimated_hours' => $p->learningPath->estimated_hours,
                    'total_items' => $p->learningPath->items->count(),
                ] : null,
                'started_at' => $p->started_at?->toISOString(),
                'completed_at' => $p->completed_at?->toISOString(),
                'progress_percentage' => $p->progress_percentage,
                'is_completed' => $p->completed_at !== null,
            ]),
        ]);
    }
}
