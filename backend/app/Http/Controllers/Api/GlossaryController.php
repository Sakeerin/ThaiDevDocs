<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Glossary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlossaryController extends Controller
{
    /**
     * List glossary terms.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Glossary::approved();

        // Filter by first letter
        if ($letter = $request->input('letter')) {
            $query->where(function ($q) use ($letter) {
                $q->where('term', 'like', $letter . '%')
                  ->orWhere('term_en', 'like', $letter . '%');
            });
        }

        $terms = $query->orderBy('term')->paginate($request->input('per_page', 50));

        return $this->successWithPagination($terms->through(fn ($term) => [
            'id' => $term->id,
            'term' => $term->term,
            'term_en' => $term->term_en,
            'slug' => $term->slug,
            'definition_short' => $term->definition_short,
        ]));
    }

    /**
     * Search glossary terms.
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:100'],
        ]);

        $query = $validated['q'];

        try {
            $results = Glossary::search($query)
                ->options(['limit' => 20])
                ->get();

            return $this->success([
                'terms' => $results->map(fn ($term) => [
                    'id' => $term->id,
                    'term' => $term->term,
                    'term_en' => $term->term_en,
                    'slug' => $term->slug,
                    'definition_short' => $term->definition_short,
                ]),
            ]);
        } catch (\Exception $e) {
            $terms = Glossary::approved()
                ->where(function ($q) use ($query) {
                    $q->where('term', 'like', "%{$query}%")
                      ->orWhere('term_en', 'like', "%{$query}%")
                      ->orWhere('definition', 'like', "%{$query}%");
                })
                ->limit(20)
                ->get();

            return $this->success([
                'terms' => $terms->map(fn ($term) => [
                    'id' => $term->id,
                    'term' => $term->term,
                    'term_en' => $term->term_en,
                    'slug' => $term->slug,
                    'definition_short' => $term->definition_short,
                ]),
            ]);
        }
    }

    /**
     * Get term by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $term = Glossary::where('slug', $slug)->approved()->first();

        if (!$term) {
            return $this->notFound('Term not found.');
        }

        // Increment view count
        $term->increment('view_count');

        return $this->success([
            'term' => [
                'id' => $term->id,
                'term' => $term->term,
                'term_en' => $term->term_en,
                'slug' => $term->slug,
                'definition' => $term->definition,
                'definition_short' => $term->definition_short,
                'pronunciation' => $term->pronunciation,
                'etymology' => $term->etymology,
                'related_terms' => $term->related_terms,
                'external_links' => $term->external_links,
                'view_count' => $term->view_count,
            ],
        ]);
    }
}
