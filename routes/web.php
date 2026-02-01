<?php

use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\Auth\TelegramController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\VideoStreamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Telegram Web App routes
Route::get('/webapp', [\App\Http\Controllers\TelegramWebAppController::class, 'index'])
    ->name('webapp.index');
Route::get('/webapp/auth-redirect', [\App\Http\Controllers\TelegramWebAppController::class, 'authRedirect'])
    ->name('webapp.auth-redirect');
Route::get('/webapp/setup-menu', [\App\Http\Controllers\TelegramWebAppController::class, 'setupMenuButton'])
    ->name('webapp.setup-menu');

// Main page - redirect to dashboard or login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

// Public catalog (if needed)
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/course/{course:slug}', [CatalogController::class, 'show'])->name('catalog.show');

// Free course enrollment (requires auth)
Route::post('/course/{course:slug}/enroll', [\App\Http\Controllers\EnrollmentController::class, 'enrollFree'])
    ->middleware('auth')
    ->name('courses.enroll');

// Guest routes (login/register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [MagicLinkController::class, 'showForm'])->name('login');
    Route::post('/login', [MagicLinkController::class, 'request']);
});

// Magic link login (accessible without auth)
Route::get('/login/{token}', [MagicLinkController::class, 'login'])->name('magic.login');

// Phone auth via Telegram (accessible without auth)
Route::post('/auth/phone', [MagicLinkController::class, 'requestPhoneAuth'])->name('auth.phone');
Route::get('/auth/phone/status', [MagicLinkController::class, 'checkPhoneAuthStatus'])->name('auth.phone.status');

// Telegram callback (old widget)
Route::get('/auth/telegram/callback', [TelegramController::class, 'callback'])->name('telegram.callback');

// Telegram bot webhook
Route::post('/telegram/webhook', [\App\Http\Controllers\TelegramBotController::class, 'webhook'])
    ->name('telegram.webhook')
    ->withoutMiddleware(['web', 'csrf']);

// Tilda payment webhook
Route::post('/webhook/tilda', [\App\Http\Controllers\TildaWebhookController::class, 'handlePayment'])
    ->name('tilda.webhook')
    ->withoutMiddleware(['web', 'csrf']);

Route::get('/webhook/tilda/test', [\App\Http\Controllers\TildaWebhookController::class, 'test'])
    ->name('tilda.webhook.test');

// Logout
Route::post('/logout', [MagicLinkController::class, 'logout'])->name('logout');

// Authenticated routes (user dashboard and courses)
Route::middleware('auth')->group(function () {
    // Dashboard - show enrolled courses
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // My Courses (enrolled)
    Route::get('/my-courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
    
    // Lesson pages
    Route::get('/my-courses/{course:slug}/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');

    // Lesson progress API
    Route::post('/my-courses/{course:slug}/lessons/{lesson}/complete', [LessonController::class, 'markComplete'])
        ->name('lessons.complete');
    Route::post('/lessons/{lesson}/video-position', [LessonController::class, 'updateVideoPosition'])
        ->name('lessons.video-position');

    // Assignment submission
    Route::post('/my-courses/{course:slug}/lessons/{lesson}/assignment', [\App\Http\Controllers\AssignmentController::class, 'submit'])
        ->name('assignments.submit');
    Route::get('/lessons/{lesson}/assignment/status', [\App\Http\Controllers\AssignmentController::class, 'show'])
        ->name('assignments.status');

    // Video streaming (Yandex Disk proxy)
    Route::get('/video/{lesson}/stream', [VideoStreamController::class, 'stream'])->name('video.stream');
    Route::get('/video/{lesson}/signed-url', [VideoStreamController::class, 'getSignedUrl'])->name('video.signed-url');
    
    // Admin preview (without enrollment check)
    Route::get('/preview/lesson/{lesson}', [LessonController::class, 'preview'])
        ->name('lessons.preview');

    // Profile settings
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])
        ->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])
        ->name('profile.password');
    Route::delete('/profile/telegram', [\App\Http\Controllers\ProfileController::class, 'disconnectTelegram'])
        ->name('profile.telegram.disconnect');
});
