<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TildaWebhookController extends Controller
{
    /**
     * Handle incoming webhook from Tilda after successful payment
     * 
     * Tilda sends form data with customer info after purchase:
     * - Phone (required)
     * - Email (optional)
     * - Name (optional)
     * - Course identifier (from hidden field or product name)
     * - Payment details
     */
    public function handlePayment(Request $request)
    {
        // Log incoming request for debugging
        Log::info('Tilda webhook received', [
            'data' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // Verify webhook secret (optional but recommended)
        $secret = config('services.tilda.webhook_secret');
        if ($secret && $request->header('X-Tilda-Secret') !== $secret) {
            Log::warning('Tilda webhook: Invalid secret');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Extract data from Tilda
        // Tilda can send data in different formats, we'll handle common ones
        $data = $request->all();
        
        // Get phone - Tilda may send as 'Phone', 'phone', 'tel', etc.
        $phone = $this->extractPhone($data);
        
        if (!$phone) {
            Log::error('Tilda webhook: No phone number provided', $data);
            return response()->json(['error' => 'Phone is required'], 400);
        }

        // Get email (optional)
        $email = $data['Email'] ?? $data['email'] ?? $data['EMAIL'] ?? null;
        
        // Get name
        $name = $data['Name'] ?? $data['name'] ?? $data['NAME'] ?? 
                $data['Имя'] ?? $data['ФИО'] ?? 'Покупатель';

        // Get course identifier - can be product name, ID, or from hidden field
        $courseIdentifier = $data['course_id'] ?? $data['Course'] ?? 
                           $data['product'] ?? $data['Product'] ?? 
                           $data['tranid'] ?? null;

        // Get payment info
        $paymentId = $data['payment_id'] ?? $data['orderid'] ?? 
                    $data['tranid'] ?? Str::uuid()->toString();
        $amount = $data['amount'] ?? $data['Amount'] ?? $data['sum'] ?? 0;

        // Normalize phone
        $normalizedPhone = $this->normalizePhone($phone);

        // Find or create user
        $user = User::where('phone', $normalizedPhone)->first();
        
        if (!$user && $email) {
            $user = User::where('email', $email)->first();
        }

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'phone' => $normalizedPhone,
                'password' => bcrypt(Str::random(16)), // Random password, user will login via Telegram
            ]);
            
            Log::info('Tilda webhook: Created new user', ['user_id' => $user->id, 'phone' => $normalizedPhone]);
        } else {
            // Update phone if not set
            if (!$user->phone) {
                $user->phone = $normalizedPhone;
                $user->save();
            }
        }

        // Find course
        $course = $this->findCourse($courseIdentifier);

        if (!$course) {
            // If no course found, try to get the first course or log error
            $course = Course::first();
            
            if (!$course) {
                Log::error('Tilda webhook: No course found', ['course_identifier' => $courseIdentifier]);
                return response()->json(['error' => 'Course not found'], 400);
            }
            
            Log::warning('Tilda webhook: Course not found, using default', [
                'requested' => $courseIdentifier,
                'used' => $course->id
            ]);
        }

        // Check if enrollment already exists
        $existingEnrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existingEnrollment) {
            Log::info('Tilda webhook: Enrollment already exists', [
                'user_id' => $user->id,
                'course_id' => $course->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Enrollment already exists',
                'enrollment_id' => $existingEnrollment->id,
            ]);
        }

        // Create enrollment
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'payment_id' => $paymentId,
            'amount_paid' => $amount,
            'payment_provider' => 'tilda',
            'enrolled_at' => now(),
        ]);

        Log::info('Tilda webhook: Enrollment created', [
            'enrollment_id' => $enrollment->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Enrollment created successfully',
            'enrollment_id' => $enrollment->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);
    }

    /**
     * Extract phone from various possible field names
     */
    protected function extractPhone(array $data): ?string
    {
        $phoneFields = ['Phone', 'phone', 'PHONE', 'tel', 'Tel', 'TEL', 
                       'Телефон', 'телефон', 'mobile', 'Mobile'];
        
        foreach ($phoneFields as $field) {
            if (!empty($data[$field])) {
                return $data[$field];
            }
        }
        
        return null;
    }

    /**
     * Normalize phone number to standard format
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);
        
        // Handle Russian numbers
        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '7' . substr($digits, 1);
        }
        
        if (strlen($digits) === 10) {
            $digits = '7' . $digits;
        }
        
        return '+' . $digits;
    }

    /**
     * Find course by various identifiers
     */
    protected function findCourse($identifier): ?Course
    {
        if (!$identifier) {
            return null;
        }

        // Try to find by ID
        if (is_numeric($identifier)) {
            $course = Course::find($identifier);
            if ($course) return $course;
        }

        // Try to find by slug
        $course = Course::where('slug', $identifier)->first();
        if ($course) return $course;

        // Try to find by title (partial match)
        $course = Course::where('title', 'like', "%{$identifier}%")->first();
        if ($course) return $course;

        return null;
    }

    /**
     * Test endpoint to verify webhook is working
     */
    public function test(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Tilda webhook endpoint is working',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
