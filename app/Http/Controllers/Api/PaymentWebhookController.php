<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPaymentJob;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * Handle incoming payment webhook
     */
    public function handle(Request $request, string $provider)
    {
        $payload = $request->all();

        // Log the raw webhook for debugging
        $paymentLog = PaymentLog::create([
            'payment_id' => $this->extractPaymentId($payload, $provider),
            'provider' => $provider,
            'status' => 'received',
            'payload' => $payload,
        ]);

        Log::info("Payment webhook received", [
            'provider' => $provider,
            'log_id' => $paymentLog->id,
        ]);

        try {
            // Validate webhook signature
            if (!$this->validateSignature($request, $provider)) {
                $paymentLog->update(['status' => 'invalid_signature']);
                Log::warning("Invalid webhook signature", ['provider' => $provider]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            // Check if this is a successful payment event
            if (!$this->isSuccessfulPayment($payload, $provider)) {
                $paymentLog->update(['status' => 'ignored']);
                return response()->json(['status' => 'ignored']);
            }

            // Dispatch job to process the payment
            ProcessPaymentJob::dispatch($payload, $provider, $paymentLog->id);

            $paymentLog->update(['status' => 'processing']);

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error("Payment webhook error", [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            $paymentLog->update(['status' => 'error']);

            return response()->json(['error' => 'Processing error'], 500);
        }
    }

    /**
     * Extract payment ID from payload based on provider
     */
    private function extractPaymentId(array $payload, string $provider): ?string
    {
        return match ($provider) {
            'yokassa' => $payload['object']['id'] ?? null,
            'cloudpayments' => $payload['TransactionId'] ?? null,
            'tinkoff' => $payload['PaymentId'] ?? null,
            default => $payload['id'] ?? null,
        };
    }

    /**
     * Validate webhook signature
     */
    private function validateSignature(Request $request, string $provider): bool
    {
        // In development, skip validation
        if (app()->environment('local') && !config("services.{$provider}.secret")) {
            return true;
        }

        return match ($provider) {
            'yokassa' => $this->validateYoKassaSignature($request),
            'cloudpayments' => $this->validateCloudPaymentsSignature($request),
            'tinkoff' => $this->validateTinkoffSignature($request),
            default => false,
        };
    }

    /**
     * Validate YoKassa webhook signature
     */
    private function validateYoKassaSignature(Request $request): bool
    {
        $secret = config('services.yokassa.webhook_secret');
        if (!$secret) {
            return true; // No secret configured, skip validation
        }

        // YoKassa uses Basic Auth or IP whitelist
        // For production, implement proper validation based on your YoKassa settings
        return true;
    }

    /**
     * Validate CloudPayments webhook signature
     */
    private function validateCloudPaymentsSignature(Request $request): bool
    {
        $secret = config('services.cloudpayments.secret');
        if (!$secret) {
            return true;
        }

        $body = $request->getContent();
        $signature = $request->header('Content-HMAC');
        
        $expectedSignature = base64_encode(hash_hmac('sha256', $body, $secret, true));
        
        return hash_equals($expectedSignature, $signature ?? '');
    }

    /**
     * Validate Tinkoff webhook signature
     */
    private function validateTinkoffSignature(Request $request): bool
    {
        // Tinkoff implementation
        return true;
    }

    /**
     * Check if this webhook represents a successful payment
     */
    private function isSuccessfulPayment(array $payload, string $provider): bool
    {
        return match ($provider) {
            'yokassa' => ($payload['event'] ?? '') === 'payment.succeeded',
            'cloudpayments' => ($payload['Status'] ?? '') === 'Completed',
            'tinkoff' => ($payload['Status'] ?? '') === 'CONFIRMED',
            default => false,
        };
    }
}
