<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * Show individual lesson
     */
    public function show(Request $request, Course $course, Lesson $lesson)
    {
        $user = $request->user();
        
        // Check access
        if (!$user->hasCourseAccess($course->id)) {
            return redirect()->route('dashboard')
                ->with('error', 'У вас нет доступа к этому курсу.');
        }

        // Ensure lesson belongs to a module of this course
        $module = $lesson->modules()->whereHas('course', function ($q) use ($course) {
            $q->where('id', $course->id);
        })->first();
        
        if (!$module) {
            abort(404, 'Урок не найден в этом курсе');
        }

        // Get or create progress record
        $progress = LessonProgress::firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['is_completed' => false, 'video_position' => 0]
        );

        // Get all lessons in course for navigation
        $allModules = $course->publishedModules()
            ->with(['publishedLessons' => function ($q) use ($user) {
                $q->with(['progress' => fn ($p) => $p->where('user_id', $user->id)]);
            }])
            ->get();

        // Build flat lessons array for prev/next navigation
        $allLessons = collect();
        foreach ($allModules as $m) {
            foreach ($m->publishedLessons as $l) {
                $l->module_title = $m->title;
                $allLessons->push($l);
            }
        }

        $currentIndex = $allLessons->search(fn($l) => $l->id === $lesson->id);
        $previousLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1] : null;

        // Load assignment if exists
        $assignment = $lesson->assignment()
            ->with(['questions.answers'])
            ->first();

        return view('lessons.show', [
            'course' => $course,
            'module' => $module,
            'lesson' => $lesson,
            'progress' => $progress,
            'assignment' => $assignment,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'allModules' => $allModules,
        ]);
    }

    /**
     * Mark lesson as completed
     */
    public function markComplete(Request $request, Course $course, Lesson $lesson)
    {
        $user = $request->user();

        if (!$user->hasCourseAccess($course->id)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $progress = LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['is_completed' => true, 'completed_at' => now()]
        );

        return response()->json([
            'success' => true,
            'is_completed' => $progress->is_completed,
        ]);
    }

    /**
     * Update video position (for resuming)
     */
    public function updateVideoPosition(Request $request, Lesson $lesson)
    {
        $user = $request->user();
        $position = $request->input('position', 0);

        LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['video_position' => $position]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Admin preview of lesson (without enrollment)
     */
    public function preview(Request $request, Lesson $lesson)
    {
        $user = $request->user();
        
        // Only admins can preview
        if (!$user->is_admin) {
            abort(403, 'Доступ только для администраторов');
        }
        
        // Get first module for context
        $module = $lesson->modules()->first();
        $course = $module?->course;

        if (!$course) {
            return back()->with('error', 'Урок не привязан к курсу.');
        }

        // Get progress (if any)
        $progress = LessonProgress::firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['is_completed' => false, 'video_position' => 0]
        );

        // Get all modules for navigation
        $allModules = $course->publishedModules()
            ->with(['publishedLessons'])
            ->get();

        // Load assignment
        $assignment = $lesson->assignment()
            ->with(['questions.answers'])
            ->first();

        return view('lessons.show', [
            'course' => $course,
            'module' => $module,
            'lesson' => $lesson,
            'progress' => $progress,
            'assignment' => $assignment,
            'previousLesson' => null,
            'nextLesson' => null,
            'allModules' => $allModules,
            'isPreview' => true,
        ]);
    }
}
