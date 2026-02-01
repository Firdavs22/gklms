<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'type',
        'title',
        'description',
        'show_correct_answers',
        'is_required',
    ];

    protected $casts = [
        'show_correct_answers' => 'boolean',
        'is_required' => 'boolean',
    ];

    /**
     * Assignment types
     */
    const TYPE_TEXT = 'text';      // Open text answer
    const TYPE_POLL = 'poll';      // Survey (no correct answers)
    const TYPE_QUIZ = 'quiz';      // Test with correct answers

    /**
     * Get the lesson this assignment belongs to
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get all questions for this assignment
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    /**
     * Check if this is a text assignment
     */
    public function isText(): bool
    {
        return $this->type === self::TYPE_TEXT;
    }

    /**
     * Check if this is a poll
     */
    public function isPoll(): bool
    {
        return $this->type === self::TYPE_POLL;
    }

    /**
     * Check if this is a quiz
     */
    public function isQuiz(): bool
    {
        return $this->type === self::TYPE_QUIZ;
    }

    /**
     * Get type label in Russian
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_TEXT => 'Вопрос с открытым ответом',
            self::TYPE_POLL => 'Опрос',
            self::TYPE_QUIZ => 'Тест',
            default => 'Неизвестно',
        };
    }
}
