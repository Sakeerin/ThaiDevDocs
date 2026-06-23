<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Glossary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlossaryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Glossary::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('term', 'like', "%{$search}%")
                    ->orWhere('term_en', 'like', "%{$search}%")
                    ->orWhere('definition', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_approved')) {
            $query->where('is_approved', $request->boolean('is_approved'));
        }

        $terms = $query->orderBy('term')
            ->paginate($request->input('per_page', 30));

        return response()->json([
            'success' => true,
            'data' => $terms->items(),
            'meta' => [
                'current_page' => $terms->currentPage(),
                'per_page' => $terms->perPage(),
                'total' => $terms->total(),
                'last_page' => $terms->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'term' => ['required', 'string', 'max:255'],
            'term_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'definition' => ['required', 'string'],
            'definition_short' => ['sometimes', 'nullable', 'string', 'max:500'],
            'pronunciation' => ['sometimes', 'nullable', 'string', 'max:255'],
            'etymology' => ['sometimes', 'nullable', 'string'],
            'related_terms' => ['sometimes', 'nullable', 'array'],
            'external_links' => ['sometimes', 'nullable', 'array'],
            'is_approved' => ['sometimes', 'boolean'],
        ]);

        $term = Glossary::create($validated);

        return response()->json([
            'success' => true,
            'data' => $term,
            'message' => 'Glossary term created successfully.',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $term = Glossary::find($id);

        if (!$term) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Glossary term not found.'],
            ], 404);
        }

        return response()->json(['success' => true, 'data' => $term]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $term = Glossary::find($id);

        if (!$term) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Glossary term not found.'],
            ], 404);
        }

        $validated = $request->validate([
            'term' => ['sometimes', 'string', 'max:255'],
            'term_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'definition' => ['sometimes', 'string'],
            'definition_short' => ['sometimes', 'nullable', 'string', 'max:500'],
            'pronunciation' => ['sometimes', 'nullable', 'string', 'max:255'],
            'etymology' => ['sometimes', 'nullable', 'string'],
            'related_terms' => ['sometimes', 'nullable', 'array'],
            'external_links' => ['sometimes', 'nullable', 'array'],
            'is_approved' => ['sometimes', 'boolean'],
        ]);

        $term->update($validated);

        return response()->json([
            'success' => true,
            'data' => $term->fresh(),
            'message' => 'Glossary term updated successfully.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $term = Glossary::find($id);

        if (!$term) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Glossary term not found.'],
            ], 404);
        }

        $term->delete();

        return response()->json([
            'success' => true,
            'message' => 'Glossary term deleted successfully.',
        ]);
    }

    public function approve(int $id): JsonResponse
    {
        $term = Glossary::find($id);

        if (!$term) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Glossary term not found.'],
            ], 404);
        }

        $term->update(['is_approved' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Glossary term approved successfully.',
        ]);
    }
}
