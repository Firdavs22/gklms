<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $existing = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return redirect()->route('courses.show', $course)
                ->with('success', 'Вы уже записаны на этот курс');
        }

        // Create enrollment
        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'payment_id' => 'FREE_' . Str::random(12),
            'amount_paid' => 0,
            'payment_provider' => 'free',
            'enrolled_at' => now(),
        ]);

        return redirect()->route('courses.show', $course)
            ->with('success', 'Вы успешно записаны на курс!');
    }
}
