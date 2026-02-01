<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    /**
     * Submit answers for an assignment
     */
    public function submit(Request $request, Course $course, Lesson $lesson)
    {
        $user = $request->user();
        
        // Check course access
        if (!$user->hasCourseAccess($course->id)) {
            return response()->json(['error' => 'Нет доступа к курсу'], 403);
        }

        // Get assignment
        $assignment = $lesson->assignment;
        if (!$assignment) {
            return response()->json(['error' => 'Задание не найдено'], 404);
        }

        // Check if already submitted
        $existing = AssignmentSubmission::where('user_id', $user->id)
            ->where('assignment_id', $assignment->id)
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Вы уже отправили ответы на это задание',
                'submission' => $existing,
            ], 422);
        }

        // Validate answers
        $answers = $request->input('answers', []);
        
        // Calculate score for quiz type
        $score = 0;
        $maxScore = 0;
        $processedAnswers = [];

        foreach ($assignment->questions as $question) {
            $questionId = (string) $question->id;
            $userAnswer = $answers[$questionId] ?? null;
            
            $processedAnswers[$question->id] = $userAnswer;

            if ($assignment->type === 'quiz') {
                $maxScore++;
                if ($question->checkAnswer($userAnswer) === true) {
                    $score++;
                }
            }
        }

        // Determine if passed (for quiz, need 70%+)
        $isPassed = true;
        if ($assignment->type === 'quiz' && $maxScore > 0) {
            $isPassed = ($score / $maxScore) >= 0.7;
        }

        // Create submission
        $submission = AssignmentSubmission::create([
            'user_id' => $user->id,
            'assignment_id' => $assignment->id,
            'answers' => $processedAnswers,
            'score' => $assignment->type === 'quiz' ? $score : null,
            'max_score' => $assignment->type === 'quiz' ? $maxScore : null,
            'is_passed' => $isPassed,
            'submitted_at' => now(),
        ]);

        // AUTOMATICALLY MARK LESSON AS COMPLETED
        if ($isPassed) {
            \App\Models\LessonProgress::updateOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                ['is_completed' => true, 'completed_at' => now()]
            );
        }

        return response()->json([
            'success' => true,
            'submission_id' => $submission->id,
            'score' => $score,
            'max_score' => $maxScore,
            'is_passed' => $isPassed,
            'message' => $isPassed ? 'Отлично! Задание выполнено.' : 'Попробуйте ещё раз.',
        ]);
    }

    /**
     * Get user's submission for an assignment
     */
    public function show(Request $request, Lesson $lesson)
    {
        $user = $request->user();
        $assignment = $lesson->assignment;
        
        if (!$assignment) {
            return response()->json(['error' => 'Задание не найдено'], 404);
        }

        $submission = AssignmentSubmission::where('user_id', $user->id)
            ->where('assignment_id', $assignment->id)
            ->first();

        if (!$submission) {
            return response()->json(['submitted' => false]);
        }

        return response()->json([
            'submitted' => true,
            'submission' => $submission,
            'score' => $submission->score,
            'max_score' => $submission->max_score,
            'is_passed' => $submission->is_passed,
        ]);
    }
}
