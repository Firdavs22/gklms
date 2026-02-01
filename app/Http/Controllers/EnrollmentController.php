<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Enroll authenticated user in a free course
     */
    public function enrollFree(Request $request, Course $course)
    {
        $user = $request->user();

        // Check if course is really free
        if (!$course->isFree()) {
            return redirect()->back()->with('error', 'Этот курс платный');
        }

        // Check if already enrolled
        $existing = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return redirect()->route('courses.show', $course)
                ->with('success', 'Вы уже записаны на этот курс');
        }

        // Create enrollment
        CourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        return redirect()->route('courses.show', $course)
            ->with('success', 'Вы успешно записаны на курс!');
    }
}
