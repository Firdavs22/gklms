<?php

use App\Http\Controllers\Api\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Payment webhooks (no auth, but signature validation)
Route::post('/payment/webhook/{provider}', [PaymentWebhookController::class, 'handle'])
    ->name('payment.webhook')
    ->withoutMiddleware(['throttle:api']);
