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

        return view('dashboard', [
            'user' => $user,
            'enrollments' => $enrollments,
        ]);
    }
}
