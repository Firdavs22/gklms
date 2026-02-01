<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebAppController extends Controller
{
    /**
     * Setup Web App menu button for the bot
     */
    public function setupMenuButton()
    {
        $botToken = config('services.telegram.bot_token');
        $appUrl = config('app.url');
        
        if (!$botToken) {
            return response()->json(['error' => 'Bot token not configured'], 500);
        }

        // Set the menu button to open web app
        $response = Http::post("https://api.telegram.org/bot{$botToken}/setChatMenuButton", [
            'menu_button' => [
                'type' => 'web_app',
                'text' => '📚 Открыть LMS',
                'web_app' => [
                    'url' => $appUrl . '/webapp',
                ]
            ]
        ]);

        if ($response->successful()) {
            Log::info('Telegram menu button set successfully');
            return response()->json(['success' => true, 'message' => 'Menu button configured']);
        }

        Log::error('Failed to set Telegram menu button', ['response' => $response->json()]);
        return response()->json(['error' => 'Failed to configure', 'details' => $response->json()], 500);
    }

    /**
     * Web App entry point - shows auth page
     */
    public function index(Request $request)
    {
        // If user is authenticated, redirect to dashboard
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        // Show a page that handles initData
        return view('webapp.index');
    }

    /**
     * Auth redirect - validates initData and logs user in
     */
    public function authRedirect(Request $request)
    {
        $initData = $request->input('initData');
        
        if (!$initData) {
            Log::warning('WebApp authRedirect: No initData');
            return redirect()->route('login')->with('error', 'Ошибка авторизации');
        }

        Log::info('WebApp authRedirect: Processing', ['initData_length' => strlen($initData)]);

        try {
            $data = $this->validateInitData($initData);
            
            if (!$data) {
                Log::warning('WebApp authRedirect: Invalid initData');
                return redirect()->route('login')->with('error', 'Неверные данные авторизации');
            }

            if (!isset($data['user'])) {
                Log::warning('WebApp authRedirect: No user in data');
                return redirect()->route('login')->with('error', 'Пользователь не найден');
            }

            $telegramUser = json_decode($data['user'], true);
            
            if (!$telegramUser || !isset($telegramUser['id'])) {
                Log::warning('WebApp authRedirect: Invalid user data');
                return redirect()->route('login')->with('error', 'Неверные данные пользователя');
            }

            // Find or create user
            $user = $this->findOrCreateUser($telegramUser);
            
            if (!$user) {
                Log::warning('WebApp authRedirect: Could not create user');
                return redirect()->route('login')->with('error', 'Ошибка создания пользователя');
            }

            // Login user
            Auth::login($user, true);
            
            Log::info('WebApp authRedirect: User logged in', ['user_id' => $user->id]);

            return redirect()->route('dashboard');
            
        } catch (\Exception $e) {
            Log::error('WebApp authRedirect error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Validate Telegram initData using bot token
     */
    private function validateInitData(string $initData): ?array
    {
        $botToken = config('services.telegram.bot_token');
        
        if (!$botToken) {
            Log::error('WebApp: Bot token not configured');
            return null;
        }

        // Parse the init data
        parse_str($initData, $data);
        
        if (!isset($data['hash'])) {
            Log::warning('WebApp: No hash in initData');
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
            Log::warning('WebApp: Hash mismatch', [
                'expected' => $calculatedHash,
                'received' => $hash
            ]);
            return null;
        }

        // Check auth_date (valid for 24 hours)
        if (isset($data['auth_date'])) {
            $authDate = (int) $data['auth_date'];
            if (time() - $authDate > 86400) {
                Log::warning('WebApp: Expired auth_date');
                return null;
            }
        }

        Log::info('WebApp: initData validated successfully');
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
            Log::info('WebApp: Found existing user', ['user_id' => $user->id]);
            return $user;
        }

        // Create new user
        $name = trim(($telegramUser['first_name'] ?? '') . ' ' . ($telegramUser['last_name'] ?? ''));
        
        $user = new User();
        $user->telegram_id = $telegramId;
        $user->name = $name ?: 'Telegram User';
        $user->password = bcrypt(uniqid());
        $user->save();

        Log::info('WebApp: Created new user', ['user_id' => $user->id, 'telegram_id' => $telegramId]);

        return $user;
    }
}

