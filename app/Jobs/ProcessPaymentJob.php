<?php

namespace App\Jobs;

use App\Models\Enrollment;
use App\Models\PaymentLog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $payload,
        public string $provider,
        public int $paymentLogId
    ) {}

    public function handle(): void
    {
        $paymentLog = PaymentLog::find($this->paymentLogId);

        try {
            // Extract data based on provider
            $data = $this->extractPaymentData();

            if (!$data['email'] || !$data['course_id']) {
                throw new \Exception("Missing required data: email or course_id");
            }

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'] ?? explode('@', $data['email'])[0]]
            );

            // Check if enrollment already exists
            $existingEnrollment = Enrollment::where('payment_id', $data['payment_id'])->first();
            
            if ($existingEnrollment) {
                Log::info("Enrollment already exists for payment", ['payment_id' => $data['payment_id']]);
                $paymentLog?->update(['status' => 'duplicate']);
                return;
            }

            // Create enrollment
            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $data['course_id'],
                'payment_id' => $data['payment_id'],
                'amount_paid' => $data['amount'],
                'payment_provider' => $this->provider,
                'enrolled_at' => now(),
            ]);

            Log::info("Enrollment created", [
                'enrollment_id' => $enrollment->id,
                'user_id' => $user->id,
                'course_id' => $data['course_id'],
            ]);

            // Send magic link for login
            $token = $user->generateMagicToken();
            SendMagicLinkEmail::dispatch($user, $token);

            $paymentLog?->update(['status' => 'completed']);

        } catch (\Exception $e) {
            Log::error("ProcessPaymentJob failed", [
                'error' => $e->getMessage(),
                'payload' => $this->payload,
            ]);

            $paymentLog?->update(['status' => 'failed']);

            throw $e;
        }
    }

    /**
     * Extract payment data based on provider
     */
    private function extractPaymentData(): array
    {
        return match ($this->provider) {
            'yokassa' => $this->extractYoKassaData(),
            'cloudpayments' => $this->extractCloudPaymentsData(),
            'tinkoff' => $this->extractTinkoffData(),
            default => throw new \Exception("Unknown provider: {$this->provider}"),
        };
    }

    /**
     * Extract data from YoKassa webhook
     */
    private function extractYoKassaData(): array
    {
        $object = $this->payload['object'] ?? [];
        $metadata = $object['metadata'] ?? [];

        return [
            'payment_id' => $object['id'] ?? null,
            'email' => $metadata['email'] ?? $object['receipt']['customer']['email'] ?? null,
            'name' => $metadata['name'] ?? null,
            'course_id' => $metadata['course_id'] ?? null,
            'amount' => $object['amount']['value'] ?? 0,
        ];
    }

    /**
     * Extract data from CloudPayments webhook
     */
    private function extractCloudPaymentsData(): array
    {
        $data = json_decode($this->payload['Data'] ?? '{}', true);

        return [
            'payment_id' => (string) ($this->payload['TransactionId'] ?? null),
            'email' => $this->payload['Email'] ?? $data['email'] ?? null,
            'name' => $this->payload['Name'] ?? $data['name'] ?? null,
            'course_id' => $data['course_id'] ?? null,
            'amount' => $this->payload['Amount'] ?? 0,
        ];
    }

    /**
     * Extract data from Tinkoff webhook
     */
    private function extractTinkoffData(): array
    {
        $data = json_decode($this->payload['DATA'] ?? '{}', true);

        return [
            'payment_id' => (string) ($this->payload['PaymentId'] ?? null),
            'email' => $data['Email'] ?? null,
            'name' => $data['Name'] ?? null,
            'course_id' => $data['CourseId'] ?? null,
            'amount' => ($this->payload['Amount'] ?? 0) / 100, // Tinkoff sends amount in kopeks
        ];
    }
}
