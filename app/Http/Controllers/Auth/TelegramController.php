<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelegramController extends Controller
{
    /**
     * Handle Telegram Login Widget callback
     */
    public function callback(Request $request)
    {
        $data = $request->all();

        // Validate Telegram data
        if (!$this->validateTelegramData($data)) {
            return redirect()->route('login')
                ->with('error', 'Ошибка авторизации через Telegram.');
        }

        $telegramId = $data['id'];
        $firstName = $data['first_name'] ?? '';
        $lastName = $data['last_name'] ?? '';
        $username = $data['username'] ?? null;

        // Find or create user by telegram_id
        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'telegram_id' => $telegramId,
                'name' => trim("{$firstName} {$lastName}") ?: $username ?: "User {$telegramId}",
                'email' => $username ? "{$username}@telegram.local" : "tg{$telegramId}@telegram.local",
                'email_verified_at' => now(),
            ]);
        }

        // Login the user
        Auth::login($user, remember: true);

        return redirect()->route('dashboard')
            ->with('success', 'Добро пожаловать!');
    }

    /**
     * Validate data from Telegram Login Widget
     */
    private function validateTelegramData(array $data): bool
    {
        $botToken = config('services.telegram.bot_token');
        
        if (!$botToken) {
            // If no bot token configured, skip validation in development
            return app()->environment('local');
        }

        $checkHash = $data['hash'] ?? null;
        unset($data['hash']);

        // Sort alphabetically
        ksort($data);

        // Create data-check-string
        $dataCheckString = collect($data)
            ->map(fn ($value, $key) => "{$key}={$value}")
            ->implode("\n");

        // Calculate hash
        $secretKey = hash('sha256', $botToken, true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        // Verify hash
        if (!hash_equals($hash, $checkHash)) {
            return false;
        }

        // Check auth_date (not older than 1 day)
        $authDate = $data['auth_date'] ?? 0;
        if ((time() - $authDate) > 86400) {
            return false;
        }

        return true;
    }
}
