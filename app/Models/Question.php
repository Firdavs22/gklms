<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'text',
        'type',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Question types
     */
    const TYPE_TEXT = 'text';          // Free text answer
    const TYPE_SINGLE = 'single';      // Single choice
    const TYPE_MULTIPLE = 'multiple';  // Multiple choice

    /**
     * Get the assignment this question belongs to
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get all answer options
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class)->orderBy('sort_order');
    }

    /**
     * Get correct answers only
     */
    public function correctAnswers(): HasMany
    {
        return $this->answers()->where('is_correct', true);
    }

    /**
     * Get user responses for this question
     */
    public function userResponses(): HasMany
    {
        return $this->hasMany(UserResponse::class);
    }

    /**
     * Check if this is a text question
     */
    public function isText(): bool
    {
        return $this->type === self::TYPE_TEXT;
    }

    /**
     * Check if this is a single choice question
     */
    public function isSingle(): bool
    {
        return $this->type === self::TYPE_SINGLE;
    }

    /**
     * Check if this is a multiple choice question
     */
    public function isMultiple(): bool
    {
        return $this->type === self::TYPE_MULTIPLE;
    }

    /**
     * Get a user's response to this question
     */
    public function getUserResponse(int $userId): ?UserResponse
    {
        return $this->userResponses()->where('user_id', $userId)->first();
    }

    /**
     * Check if user has responded to this question
     */
    public function hasUserResponded(int $userId): bool
    {
        return $this->userResponses()->where('user_id', $userId)->exists();
    }

    /**
     * Get correct answer IDs
     */
    public function getCorrectAnswersAttribute(): array
    {
        return $this->correctAnswers()->pluck('id')->toArray();
    }

    /**
     * Check if user's answer is correct
     */
    public function checkAnswer($userAnswer): ?bool
    {
        // Text questions don't have correct answers
        if ($this->type === self::TYPE_TEXT) {
            return null;
        }

        $correctIds = $this->correct_answers;

        if ($this->type === self::TYPE_SINGLE) {
            return in_array((int) $userAnswer, $correctIds);
        }

        if ($this->type === self::TYPE_MULTIPLE) {
            $userIds = is_array($userAnswer) ? array_map('intval', $userAnswer) : [];
            sort($userIds);
            sort($correctIds);
            return $userIds === $correctIds;
        }

        return null;
    }
}
