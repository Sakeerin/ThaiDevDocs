<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'website',
        'github_username',
        'role',
        'contribution_points',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the user's preferences.
     */
    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    /**
     * Get articles authored by this user.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    /**
     * Get articles reviewed by this user.
     */
    public function reviewedArticles(): HasMany
    {
        return $this->hasMany(Article::class, 'reviewer_id');
    }

    /**
     * Get user's bookmarks.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get user's collections.
     */
    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    /**
     * Get user's reading history.
     */
    public function readingHistory(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }

    /**
     * Get user's comments.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get user's contributions.
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    /**
     * Get user's edit suggestions.
     */
    public function editSuggestions(): HasMany
    {
        return $this->hasMany(EditSuggestion::class);
    }

    /**
     * Get user's learning progress.
     */
    public function learningProgress(): HasMany
    {
        return $this->hasMany(UserLearningProgress::class);
    }

    /**
     * Get learning paths created by this user.
     */
    public function authoredLearningPaths(): HasMany
    {
        return $this->hasMany(LearningPath::class, 'author_id');
    }

    /**
     * Get user's article ratings.
     */
    public function articleRatings(): HasMany
    {
        return $this->hasMany(ArticleRating::class);
    }

    /**
     * Check if user has bookmarked an article.
     */
    public function hasBookmarked(Article $article): bool
    {
        return $this->bookmarks()->where('article_id', $article->id)->exists();
    }

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * Check if user is editor or higher.
     */
    public function isEditor(): bool
    {
        return in_array($this->role, ['editor', 'admin', 'super_admin']);
    }

    /**
     * Check if user is contributor or higher.
     */
    public function isContributor(): bool
    {
        return in_array($this->role, ['contributor', 'editor', 'admin', 'super_admin']);
    }
}
