<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContributionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Contribution::with([
            'user:id,name,email,avatar',
            'article:id,title,slug',
            'reviewer:id,name',
        ])->orderByDesc('created_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $contributions = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $contributions->items(),
            'meta' => [
                'current_page' => $contributions->currentPage(),
                'per_page' => $contributions->perPage(),
                'total' => $contributions->total(),
                'last_page' => $contributions->lastPage(),
            ],
        ]);
    }

    public function pending(): JsonResponse
    {
        $contributions = Contribution::pending()
            ->with([
                'user:id,name,email,avatar',
                'article:id,title,slug',
            ])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contributions,
        ]);
    }

    public function review(Request $request, int $id): JsonResponse
    {
        $contribution = Contribution::with('user')->find($id);

        if (!$contribution) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Contribution not found.'],
            ], 404);
        }

        if ($contribution->status !== 'pending') {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'ALREADY_REVIEWED', 'message' => 'This contribution has already been reviewed.'],
            ], 400);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'review_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'points' => ['sometimes', 'integer', 'min:0', 'max:1000'],
        ]);

        $contribution->update([
            'status' => $validated['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
            'points' => $validated['points'] ?? $contribution->points,
        ]);

        if ($validated['status'] === 'approved' && $contribution->user) {
            $contribution->user->increment('contribution_points', $contribution->points);
        }

        return response()->json([
            'success' => true,
            'data' => $contribution->fresh(['user:id,name', 'article:id,title', 'reviewer:id,name']),
            'message' => 'Contribution reviewed successfully.',
        ]);
    }
}
