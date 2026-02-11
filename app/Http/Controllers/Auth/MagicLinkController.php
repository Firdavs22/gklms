<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendMagicLinkEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class MagicLinkController extends Controller
{
    /**
     * Show the login form
     */
    public function showForm()
    {
        return view('auth.login');
    }

    /**
     * Request a magic link
     */
    public function request(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');

        // Find or create user
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => explode('@', $email)[0]]
        );

        // Generate magic token
        $token = $user->generateMagicToken();

        // Send email with magic link
        SendMagicLinkEmail::dispatch($user, $token);

        return view('auth.magic-link-sent', ['email' => $email]);
    }

    /**
     * Login via magic link
     */
    public function login(string $token)
    {
        $user = User::where('magic_token', $token)
            ->where('magic_token_expires_at', '>', now())
            ->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Ссылка недействительна или истекла. Запросите новую.');
        }

        // Clear the token
        $user->clearMagicToken();

        // Mark email as verified
        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        // Login the user
        Auth::login($user, remember: true);

        return redirect()->route('dashboard')
            ->with('success', 'Добро пожаловать!');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Request phone authentication via Telegram
     */
    public function requestPhoneAuth(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:20',
        ]);

        $phone = $this->normalizePhone($request->input('phone'));

        // Check if user is already registered with telegram_id
        $existingUser = User::where('phone', $phone)->whereNotNull('telegram_id')->first();

        if ($existingUser) {
            // Registered user — send 6-digit code directly to Telegram
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Store code in cache for 5 minutes
            \Illuminate\Support\Facades\Cache::put("tg_login_code:{$phone}", [
                'code' => $code,
                'user_id' => $existingUser->id,
                'attempts' => 0,
            ], now()->addMinutes(5));

            // Send code via Telegram Bot API
            $this->sendLoginCode($existingUser->telegram_id, $code);

            return response()->json([
                'success' => true,
                'mode' => 'code',
                'phone' => $phone,
                'message' => 'Код отправлен в Telegram',
            ]);
        }

        // New user or no telegram_id — use deep link flow
        $authToken = \Illuminate\Support\Str::uuid()->toString();

        // Store phone -> token mapping in cache
        \Illuminate\Support\Facades\Cache::put("tg_auth:{$authToken}", [
            'phone' => $phone,
            'status' => 'pending',
        ], now()->addMinutes(5));

        // Track tokens for reverse lookup
        $keys = \Illuminate\Support\Facades\Cache::get('tg_auth_keys', []);
        $keys[] = $authToken;
        // Keep only recent 100 tokens
        $keys = array_slice($keys, -100);
        \Illuminate\Support\Facades\Cache::put('tg_auth_keys', $keys, now()->addMinutes(10));

        // Generate deep link
        $botUsername = config('services.telegram.bot_username', 'GloboKidsBot');
        $deepLink = "https://t.me/{$botUsername}?start={$authToken}";

        return response()->json([
            'success' => true,
            'mode' => 'deeplink',
            'token' => $authToken,
            'deep_link' => $deepLink,
            'bot_username' => $botUsername,
        ]);
    }

    /**
     * Verify Telegram login code for registered users
     */
    public function verifyTelegramCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:20',
            'code' => 'required|string|size:6',
        ]);

        $phone = $this->normalizePhone($request->input('phone'));
        $code = $request->input('code');

        $cacheKey = "tg_login_code:{$phone}";
        $codeData = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if (!$codeData) {
            return response()->json([
                'success' => false,
                'message' => 'Код истёк. Запросите новый.',
            ], 410);
        }

        // Check max attempts (5)
        if ($codeData['attempts'] >= 5) {
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
            return response()->json([
                'success' => false,
                'message' => 'Слишком много попыток. Запросите новый код.',
            ], 429);
        }

        // Increment attempts
        $codeData['attempts']++;
        \Illuminate\Support\Facades\Cache::put($cacheKey, $codeData, now()->addMinutes(5));

        if ($codeData['code'] !== $code) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный код',
            ], 422);
        }

        // Code is correct — login the user
        $user = User::find($codeData['user_id']);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не найден',
            ], 404);
        }

        // Clear the code
        \Illuminate\Support\Facades\Cache::forget($cacheKey);

        // Login
        Auth::login($user, remember: true);

        return response()->json([
            'success' => true,
            'message' => 'Авторизация успешна',
            'redirect' => route('dashboard'),
        ]);
    }

    /**
     * Send login code to Telegram
     */
    protected function sendLoginCode(string|int $telegramId, string $code): void
    {
        $botToken = config('services.telegram.bot_token');

        if (!$botToken) {
            return;
        }

        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $telegramId,
            'text' => "🔐 *Код для входа*\n\n`{$code}`\n\n_Действителен 5 минут_",
            'parse_mode' => 'Markdown',
        ]);
    }

    /**
     * Check phone auth status (polling)
     */
    public function checkPhoneAuthStatus(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $authToken = $request->input('token');

        $authData = \Illuminate\Support\Facades\Cache::get("tg_auth:{$authToken}");

        if (!$authData) {
            return response()->json([
                'status' => 'expired',
                'message' => 'Сессия авторизации истекла',
            ], 410);
        }

        if ($authData['status'] === 'success' && isset($authData['user_id'])) {
            // Auth successful! Login the user
            $user = User::find($authData['user_id']);

            if ($user) {
                // Clear the token
                \Illuminate\Support\Facades\Cache::forget("tg_auth:{$authToken}");

                // Login
                Auth::login($user, remember: true);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Авторизация успешна',
                    'redirect' => route('dashboard'),
                ]);
            }
        }

        // Still pending
        return response()->json([
            'status' => 'pending',
            'message' => 'Ожидание подтверждения в Telegram',
        ]);
    }

    /**
     * Normalize phone number
     */
    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 11 && str_starts_with($phone, '8')) {
            $phone = '7' . substr($phone, 1);
        }

        if (strlen($phone) === 10) {
            $phone = '7' . $phone;
        }

        return $phone;
    }
}
