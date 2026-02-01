<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendMagicLinkEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        
        // Generate unique auth token
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
            'token' => $authToken,
            'deep_link' => $deepLink,
            'bot_username' => $botUsername,
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
