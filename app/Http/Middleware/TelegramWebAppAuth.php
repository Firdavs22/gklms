<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TelegramWebAppAuth
{
    /**
     * Handle Telegram Web App authentication
     */
    public function handle(Request $request, Closure $next)
    {
        $initData = $request->header('X-Telegram-Init-Data') ?? $request->input('initData');
        
        if (!$initData) {
            return $next($request);
        }

        try {
            $data = $this->validateInitData($initData);
            
            if ($data && isset($data['user'])) {
                $telegramUser = json_decode($data['user'], true);
                
                if ($telegramUser && isset($telegramUser['id'])) {
                    $user = $this->findOrCreateUser($telegramUser);
                    
                    if ($user) {
                        Auth::login($user);
                        $request->attributes->set('telegram_webapp', true);
                        $request->attributes->set('telegram_user', $telegramUser);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Telegram WebApp auth error: ' . $e->getMessage());
        }

        return $next($request);
    }

    /**
     * Validate Telegram initData using bot token
     */
    private function validateInitData(string $initData): ?array
    {
        $botToken = config('services.telegram.bot_token');
        
        if (!$botToken) {
            return null;
        }

        // Parse the init data
        parse_str($initData, $data);
        
        if (!isset($data['hash'])) {
            return null;
        }

        $hash = $data['hash'];
        unset($data['hash']);

        // Sort alphabetically
        ksort($data);

        // Create data check string
        $dataCheckString = collect($data)
            ->map(fn($value, $key) => "$key=$value")
            ->implode("\n");

        // Create secret key
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        
        // Calculate hash
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        // Verify hash
        if (!hash_equals($calculatedHash, $hash)) {
            Log::warning('Telegram WebApp: Invalid hash');
            return null;
        }

        // Check auth_date (valid for 24 hours)
        if (isset($data['auth_date'])) {
            $authDate = (int) $data['auth_date'];
            if (time() - $authDate > 86400) {
                Log::warning('Telegram WebApp: Expired auth_date');
                return null;
            }
        }

        return $data;
    }

    /**
     * Find or create user from Telegram data
     */
    private function findOrCreateUser(array $telegramUser): ?User
    {
        $telegramId = (string) $telegramUser['id'];
        
        // Try to find by telegram_id
        $user = User::where('telegram_id', $telegramId)->first();
        
        if ($user) {
            return $user;
        }

        // Create new user
        $name = trim(($telegramUser['first_name'] ?? '') . ' ' . ($telegramUser['last_name'] ?? ''));
        
        $user = new User();
        $user->telegram_id = $telegramId;
        $user->name = $name ?: 'Telegram User';
        $user->password = bcrypt(uniqid());
        $user->save();

        Log::info('Created user from Telegram WebApp', ['telegram_id' => $telegramId, 'name' => $name]);

        return $user;
    }
}
