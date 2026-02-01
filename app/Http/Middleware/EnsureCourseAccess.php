<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCourseAccess
{
    /**
     * Check that user has access to the course (purchased it)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $course = $request->route('course');
        
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Войдите в личный кабинет для просмотра курса.');
        }

        // Check if user has access to this course
        if (!$user->hasCourseAccess($course->id)) {
            return redirect()->route('dashboard')
                ->with('error', 'У вас нет доступа к этому курсу. Приобретите его на сайте.');
        }

        return $next($request);
    }
}
