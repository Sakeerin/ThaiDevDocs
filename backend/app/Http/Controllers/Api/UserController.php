<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReadingHistory;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Get user profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['preferences', 'bookmarks', 'collections']);

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'bio' => $user->bio,
                'website' => $user->website,
                'github_username' => $user->github_username,
                'role' => $user->role,
                'contribution_points' => $user->contribution_points,
                'email_verified' => $user->hasVerifiedEmail(),
                'created_at' => $user->created_at?->toISOString(),
                'stats' => [
                    'bookmarks_count' => $user->bookmarks->count(),
                    'collections_count' => $user->collections->count(),
                    'contributions_count' => $user->contributions()->approved()->count(),
                ],
            ],
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],
            'github_username' => ['sometimes', 'nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9-]+$/'],
        ]);

        $user->update($validated);

        return $this->success([
            'message' => 'Profile updated successfully.',
        ]);
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'theme' => ['sometimes', 'in:light,dark,system'],
            'font_size' => ['sometimes', 'in:small,medium,large'],
            'code_theme' => ['sometimes', 'string', 'max:50'],
            'email_notifications' => ['sometimes', 'boolean'],
            'weekly_digest' => ['sometimes', 'boolean'],
        ]);

        $preferences = $user->preferences ?? UserPreference::create(['user_id' => $user->id]);
        $preferences->update($validated);

        return $this->success([
            'preferences' => $preferences->fresh(),
        ], 'Preferences updated successfully.');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return $this->error(
                'The current password is incorrect.',
                'INVALID_PASSWORD',
                422
            );
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Revoke all other tokens
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return $this->success(null, 'Password updated successfully.');
    }

    /**
     * Get reading history.
     */
    public function history(Request $request): JsonResponse
    {
        $history = $request->user()
            ->readingHistory()
            ->with('article:id,title,slug,summary,reading_time,difficulty')
            ->orderByDesc('last_read_at')
            ->paginate($request->input('per_page', 20));

        return $this->successWithPagination($history);
    }

    /**
     * Record reading history.
     */
    public function recordHistory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'article_id' => ['required', 'exists:articles,id'],
            'progress' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'time_spent' => ['sometimes', 'integer', 'min:0'],
        ]);

        $history = ReadingHistory::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'article_id' => $validated['article_id'],
            ],
            [
                'progress' => $validated['progress'] ?? 0,
                'time_spent' => \DB::raw('time_spent + ' . ($validated['time_spent'] ?? 0)),
                'last_read_at' => now(),
            ]
        );

        return $this->success(['history' => $history]);
    }

    /**
     * Clear reading history.
     */
    public function clearHistory(Request $request): JsonResponse
    {
        $request->user()->readingHistory()->delete();

        return $this->success(null, 'Reading history cleared.');
    }

    /**
     * Get user contributions.
     */
    public function contributions(Request $request): JsonResponse
    {
        $contributions = $request->user()
            ->contributions()
            ->with('article:id,title,slug')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return $this->successWithPagination($contributions);
    }
}
