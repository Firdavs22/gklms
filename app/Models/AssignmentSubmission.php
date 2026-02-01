<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'assignment_id',
        'answers',
        'score',
        'max_score',
        'is_passed',
        'submitted_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'is_passed' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the user who submitted
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the assignment
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Calculate score percentage
     */
    public function getScorePercentageAttribute(): ?float
    {
        if (!$this->max_score) {
            return null;
        }
        return round(($this->score / $this->max_score) * 100, 1);
    }

    /**
     * Get formatted answers for display
     */
    public function getFormattedAnswersAttribute(): array
    {
        $formatted = [];
        $assignment = $this->assignment;
        
        if (!$assignment || !is_array($this->answers)) {
            return $formatted;
        }

        foreach ($assignment->questions as $question) {
            $userAnswer = $this->answers[$question->id] ?? null;
            
            $formatted[] = [
                'question' => $question->text,
                'type' => $question->type,
                'user_answer' => $userAnswer,
                'correct_answer' => $question->getCorrectAnswersAttribute(),
                'is_correct' => $question->checkAnswer($userAnswer),
            ];
        }

        return $formatted;
    }
}
