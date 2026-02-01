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

        return view('dashboard', [
            'user' => $user,
            'enrollments' => $enrollments,
            'completedLessons' => $completedLessons,
            'totalLessons' => $totalLessons,
        ]);
    }
}
