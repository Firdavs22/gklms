<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'telegram_id',
        'magic_token',
        'magic_token_expires_at',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'magic_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'magic_token_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Check if user can access Filament admin panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    /**
     * Get all enrollments for this user
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get all lesson progress records
     */
    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * Get all user responses
     */
    public function responses(): HasMany
    {
        return $this->hasMany(UserResponse::class);
    }

    /**
     * Check if user has access to a specific course
     */
    public function hasCourseAccess(int $courseId): bool
    {
        return $this->enrollments()->where('course_id', $courseId)->exists();
    }

    /**
     * Get progress for a specific lesson
     */
    public function getLessonProgress(int $lessonId): ?LessonProgress
    {
        return $this->lessonProgress()->where('lesson_id', $lessonId)->first();
    }

    /**
     * Check if user has completed a lesson
     */
    public function hasCompletedLesson(int $lessonId): bool
    {
        return $this->lessonProgress()
            ->where('lesson_id', $lessonId)
            ->where('is_completed', true)
            ->exists();
    }

    /**
     * Get course progress percentage
     */
    public function getCourseProgress(Course $course): float
    {
        $totalLessons = $course->lessons()->count();
        
        if ($totalLessons === 0) {
            return 0;
        }

        $completedLessons = $this->lessonProgress()
            ->whereHas('lesson', fn ($q) => $q->whereHas('module', fn ($q2) => $q2->where('course_id', $course->id)))
            ->where('is_completed', true)
            ->count();

        return round(($completedLessons / $totalLessons) * 100, 1);
    }

    /**
     * Generate a magic link token for passwordless auth
     */
    public function generateMagicToken(): string
    {
        $token = Str::random(64);
        
        $this->update([
            'magic_token' => $token,
            'magic_token_expires_at' => now()->addHours(24),
        ]);

        return $token;
    }

    /**
     * Check if the magic token is valid
     */
    public function isValidMagicToken(string $token): bool
    {
        return $this->magic_token === $token 
            && $this->magic_token_expires_at 
            && $this->magic_token_expires_at->isFuture();
    }

    /**
     * Clear the magic token after use
     */
    public function clearMagicToken(): void
    {
        $this->update([
            'magic_token' => null,
            'magic_token_expires_at' => null,
        ]);
    }
}
