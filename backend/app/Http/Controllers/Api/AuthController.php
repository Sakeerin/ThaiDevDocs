<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Create default preferences
        UserPreference::create([
            'user_id' => $user->id,
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => $this->formatUser($user),
            'token' => $token,
        ], 'Registration successful. Please verify your email.', 201);
    }

    /**
     * Login user and create token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error(
                'The provided credentials are incorrect.',
                'INVALID_CREDENTIALS',
                401
            );
        }

        $user = User::where('email', $validated['email'])->first();
        
        // Update last login
        $user->update(['last_login_at' => now()]);

        // Create token with expiration based on remember
        $expiration = $request->boolean('remember') ? null : now()->addDays(7);
        $token = $user->createToken('auth-token', ['*'], $expiration)->plainTextToken;

        return $this->success([
            'user' => $this->formatUser($user),
            'token' => $token,
        ], 'Login successful.');
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully.');
    }

    /**
     * Refresh token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'token' => $token,
        ], 'Token refreshed.');
    }

    /**
     * Get current authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('preferences');

        return $this->success([
            'user' => $this->formatUser($user),
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
            'github_username' => ['sometimes', 'nullable', 'string', 'max:255'],
            'avatar' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $user->update($validated);

        return $this->success([
            'user' => $this->formatUser($user->fresh()),
        ], 'Profile updated successfully.');
    }

    /**
     * Request password reset link.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success(null, 'Password reset link sent to your email.');
        }

        return $this->error(
            'Unable to send reset link.',
            'RESET_LINK_FAILED',
            400
        );
    }

    /**
     * Reset password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                // Revoke all tokens
                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->success(null, 'Password has been reset successfully.');
        }

        return $this->error(
            'Unable to reset password.',
            'RESET_FAILED',
            400
        );
    }

    /**
     * Social login redirect.
     */
    public function socialLogin(Request $request, string $provider): JsonResponse
    {
        $validated = $request->validate([
            'access_token' => ['required', 'string'],
        ]);

        if (!in_array($provider, ['google', 'github'])) {
            return $this->error('Invalid provider.', 'INVALID_PROVIDER', 400);
        }

        try {
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->userFromToken($validated['access_token']);

            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(str()->random(32)),
                    'avatar' => $socialUser->getAvatar(),
                    'email_verified_at' => now(),
                    'github_username' => $provider === 'github' ? $socialUser->getNickname() : null,
                ]);

                // Create default preferences
                UserPreference::create([
                    'user_id' => $user->id,
                ]);
            }

            // Update last login
            $user->update(['last_login_at' => now()]);

            $token = $user->createToken('auth-token')->plainTextToken;

            return $this->success([
                'user' => $this->formatUser($user),
                'token' => $token,
                'is_new_user' => $user->wasRecentlyCreated,
            ], 'Login successful.');

        } catch (\Exception $e) {
            return $this->error(
                'Social authentication failed.',
                'SOCIAL_AUTH_FAILED',
                400
            );
        }
    }

    /**
     * Format user data for response.
     */
    protected function formatUser(User $user): array
    {
        return [
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
            'preferences' => $user->preferences ? [
                'theme' => $user->preferences->theme,
                'font_size' => $user->preferences->font_size,
                'code_theme' => $user->preferences->code_theme,
                'email_notifications' => $user->preferences->email_notifications,
                'weekly_digest' => $user->preferences->weekly_digest,
            ] : null,
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}
