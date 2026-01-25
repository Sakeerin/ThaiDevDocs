<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * List all categories.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::active()
            ->with(['children' => function ($q) {
                $q->active()->orderBy('sort_order');
            }])
            ->withCount(['topics' => function ($q) {
                $q->active();
            }]);

        // Only root categories by default
        if ($request->boolean('root', true)) {
            $query->root();
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        $categories = $query->orderBy('sort_order')->get();

        return $this->success([
            'categories' => $categories->map(fn ($cat) => $this->formatCategory($cat)),
        ]);
    }

    /**
     * Get category by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->active()
            ->with(['parent', 'children' => function ($q) {
                $q->active()->orderBy('sort_order');
            }])
            ->first();

        if (!$category) {
            return $this->notFound('Category not found.');
        }

        return $this->success([
            'category' => $this->formatCategory($category, true),
        ]);
    }

    /**
     * Get topics in a category.
     */
    public function topics(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->active()->first();

        if (!$category) {
            return $this->notFound('Category not found.');
        }

        $topics = $category->topics()
            ->active()
            ->withCount(['articles' => function ($q) {
                $q->published();
            }])
            ->orderBy('sort_order')
            ->get();

        return $this->success([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
            'topics' => $topics->map(fn ($topic) => [
                'id' => $topic->id,
                'name' => $topic->name,
                'name_en' => $topic->name_en,
                'slug' => $topic->slug,
                'description' => $topic->description,
                'icon' => $topic->icon,
                'article_count' => $topic->articles_count,
            ]),
        ]);
    }

    /**
     * Format category for response.
     */
    protected function formatCategory(Category $category, bool $detailed = false): array
    {
        $data = [
            'id' => $category->id,
            'name' => $category->name,
            'name_en' => $category->name_en,
            'slug' => $category->slug,
            'description' => $category->description,
            'icon' => $category->icon,
            'color' => $category->color,
            'is_featured' => $category->is_featured,
            'topics_count' => $category->topics_count ?? $category->topics()->active()->count(),
        ];

        if ($category->children && $category->children->isNotEmpty()) {
            $data['children'] = $category->children->map(fn ($child) => [
                'id' => $child->id,
                'name' => $child->name,
                'slug' => $child->slug,
                'icon' => $child->icon,
            ]);
        }

        if ($detailed) {
            $data['meta_title'] = $category->meta_title;
            $data['meta_description'] = $category->meta_description;
            $data['parent'] = $category->parent ? [
                'id' => $category->parent->id,
                'name' => $category->parent->name,
                'slug' => $category->parent->slug,
            ] : null;
        }

        return $data;
    }
}
