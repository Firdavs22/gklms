<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show user's dashboard with enrolled courses
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $enrollments = $user->enrollments()
            ->with('course')
            ->latest('enrolled_at')
            ->get();

        // Calculate statistics
        $completedLessons = $user->lessonProgress()->where('is_completed', true)->count();
        
        $totalLessons = 0;
        foreach ($enrollments as $enrollment) {
            $totalLessons += $enrollment->course->lessons()->where('lessons.is_published', true)->count();
        }

        // Determine "Last Viewed" logic
        $lastProgress = $user->lessonProgress()
            ->with(['lesson.modules.course'])
            ->latest('updated_at')
            ->first();

        // Safe fallback if lesson or module is missing
        $lastLessonModule = $lastProgress?->lesson->modules->first();
        $lastLessonCourse = $lastLessonModule?->course;

        $lastLessonUrl = ($lastProgress && $lastLessonModule && $lastLessonCourse)
            ? route('lessons.show', [$lastLessonCourse, $lastProgress->lesson]) 
            : '#';

        $lastCourseUrl = $lastLessonCourse
            ? route('courses.show', $lastLessonCourse)
            : ($enrollments->first() ? route('courses.show', $enrollments->first()->course) : route('catalog.index'));

        if ($totalLessons == 0 && $enrollments->isNotEmpty()) {
             // Fallback if no lessons found (edge case) but has course
             $lastLessonUrl = route('courses.show', $enrollments->first()->course);
        }

        return view('dashboard', [
            'user' => $user,
            'enrollments' => $enrollments,
            'completedLessons' => $completedLessons,
            'totalLessons' => $totalLessons,
            'lastLessonUrl' => $lastLessonUrl,
            'lastCourseUrl' => $lastCourseUrl,
            'hasStarted' => $lastProgress !== null,
        ]);
    }
}
