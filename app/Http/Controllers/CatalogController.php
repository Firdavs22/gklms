<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Show public catalog with all published courses
     */
    public function index()
    {
        $courses = Course::published()
            ->withCount(['modules', 'enrollments'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('catalog.index', [
            'courses' => $courses,
        ]);
    }

    /**
     * Show public course page
     */
    public function show(Course $course)
    {
        // Only show published courses
        if (!$course->is_published) {
            abort(404);
        }

        $modules = $course->publishedModules()
            ->with(['publishedLessons'])
            ->get();

        $totalLessons = $modules->sum(fn ($m) => $m->publishedLessons->count());

        return view('catalog.show', [
            'course' => $course,
            'modules' => $modules,
            'totalLessons' => $totalLessons,
        ]);
    }
}
