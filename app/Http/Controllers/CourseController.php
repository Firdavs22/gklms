<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Show course with its modules and lessons
     */
    public function show(Request $request, Course $course)
    {
        $user = $request->user();
        
        // Check access
        if (!$user->hasCourseAccess($course->id)) {
            return redirect()->route('dashboard')
                ->with('error', 'У вас нет доступа к этому курсу.');
        }

        // Load modules with lessons
        $modules = $course->publishedModules()
            ->with(['publishedLessons' => function ($q) {
                // Ensure lessons are ordered correctly within the module
                $q->orderBy('lesson_module.sort_order');
            }])
            ->get();

        // Collect all lesson IDs to fetch progress efficiently
        $allLessonIds = $modules->flatMap(fn($m) => $m->publishedLessons)->pluck('id')->unique();
        $totalLessons = $allLessonIds->count();

        // Fetch completed lesson IDs for this user
        $completedLessonIds = \App\Models\LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $allLessonIds)
            ->where('is_completed', true)
            ->pluck('lesson_id')
            ->flip() // Flip for faster lookup (id => index)
            ->toArray();

        $completedCount = count($completedLessonIds);
        
        $progressPercent = $totalLessons > 0 
            ? round(($completedCount / $totalLessons) * 100) 
            : 0;

        return view('courses.show', [
            'course' => $course,
            'modules' => $modules,
            'progressPercent' => $progressPercent,
            'completedLessons' => $completedCount,
            'totalLessons' => $totalLessons,
            'completedLessonIds' => $completedLessonIds, // Pass lookup array
        ]);
    }
}
