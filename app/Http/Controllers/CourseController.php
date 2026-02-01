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

        // Load modules with lessons and progress
        $modules = $course->publishedModules()
            ->with(['publishedLessons' => function ($q) use ($user) {
                $q->with(['progress' => fn ($p) => $p->where('user_id', $user->id)]);
            }])
            ->get();

        // Calculate progress
        $totalLessons = 0;
        $completedLessons = 0;
        
        foreach ($modules as $module) {
            foreach ($module->publishedLessons as $lesson) {
                $totalLessons++;
                if ($lesson->progress->first()?->is_completed) {
                    $completedLessons++;
                }
            }
        }
        
        $progressPercent = $totalLessons > 0 
            ? round(($completedLessons / $totalLessons) * 100) 
            : 0;

        return view('courses.show', [
            'course' => $course,
            'modules' => $modules,
            'progressPercent' => $progressPercent,
            'completedLessons' => $completedLessons,
            'totalLessons' => $totalLessons,
        ]);
    }
}
