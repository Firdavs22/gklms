<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramBotController extends Controller
{
    protected TelegramBotService $bot;

    public function __construct(TelegramBotService $bot)
    {
        $this->bot = $bot;
    }

    /**
     * Handle incoming webhook from Telegram
     */
    public function webhook(Request $request)
    {
        $update = $request->all();
        
        Log::info('Telegram webhook received', $update);

        // Handle message
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        return response('OK', 200);
    }

    /**
     * Process incoming message
     */
    protected function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];

        // Handle /start command with token
        if (isset($message['text']) && str_starts_with($message['text'], '/start')) {
            $this->handleStartCommand($chatId, $message['text']);
            return;
        }

        // Handle contact sharing
        if (isset($message['contact'])) {
            $this->handleContact($chatId, $message['contact']);
            return;
        }

        // Default response
        $this->bot->sendMessage($chatId, 
            "👋 Привет! Для авторизации перейдите на сайт и введите номер телефона."
        );
    }

    /**
     * Handle /start command with auth token
     */
    protected function handleStartCommand(int $chatId, string $text): void
    {
        $parts = explode(' ', $text);
        
        if (count($parts) < 2) {
            // Check if user already exists by telegram_id
            $existingUser = User::where('telegram_id', $chatId)->first();
            if ($existingUser) {
                $this->bot->sendSuccessMessage($chatId,
                    "👋 Привет, <b>{$existingUser->name}</b>!\n\n" .
                    "Вы уже зарегистрированы. Для входа перейдите на сайт и введите свой номер телефона."
                );
                return;
            }
            
            $this->bot->sendMessage($chatId,
                "👋 Привет! Для авторизации перейдите на сайт и введите свой номер телефона."
            );
            return;
        }

        $authToken = $parts[1];

        // Check if token exists in cache
        $phoneData = Cache::get("tg_auth:{$authToken}");
        
        if (!$phoneData) {
            $this->bot->sendMessage($chatId,
                "❌ Ссылка устарела или недействительна.\n\nВернитесь на сайт и попробуйте снова."
            );
            return;
        }

        // Check if user already exists by this phone
        $existingUser = User::where('phone', $this->normalizePhone($phoneData['phone']))->first();
        if ($existingUser && $existingUser->telegram_id) {
            // User exists, auto-login
            Cache::put("tg_auth:{$authToken}", [
                'phone' => $phoneData['phone'],
                'user_id' => $existingUser->id,
                'status' => 'success',
            ], now()->addMinutes(5));
            
            $this->bot->sendSuccessMessage($chatId,
                "✅ Добро пожаловать, <b>{$existingUser->name}</b>!\n\n" .
                "Вернитесь на сайт — вы уже авторизованы."
            );
            return;
        }

        // Store chat_id with authToken for later lookup (more reliable)
        Cache::put("tg_chat_token:{$chatId}", $authToken, now()->addMinutes(5));
        Cache::put("tg_chat:{$authToken}", $chatId, now()->addMinutes(5));

        // Request phone number
        $this->bot->requestContact($chatId,
            "📱 Для входа в <b>GloboKids</b> нажмите кнопку ниже, чтобы подтвердить ваш номер телефона.\n\n" .
            "Номер, указанный на сайте: <b>{$phoneData['phone']}</b>"
        );
    }

    /**
     * Handle contact (phone number) sharing
     */
    protected function handleContact(int $chatId, array $contact): void
    {
        $telegramPhone = $this->normalizePhone($contact['phone_number']);
        
        Log::info("Contact received", ['phone' => $telegramPhone, 'chat_id' => $chatId]);

        // Find auth token by chat_id
        $authToken = $this->findTokenByChatId($chatId);
        
        if (!$authToken) {
            $this->bot->sendMessage($chatId,
                "❌ Не найдена активная сессия авторизации.\n\nВернитесь на сайт и попробуйте снова."
            );
            return;
        }

        // Get phone data from cache
        $phoneData = Cache::get("tg_auth:{$authToken}");
        
        if (!$phoneData) {
            $this->bot->sendMessage($chatId,
                "❌ Сессия авторизации истекла.\n\nВернитесь на сайт и попробуйте снова."
            );
            return;
        }

        $expectedPhone = $this->normalizePhone($phoneData['phone']);

        // Verify phone matches
        if ($telegramPhone !== $expectedPhone) {
            $this->bot->sendMessage($chatId,
                "❌ Номер телефона не совпадает с указанным на сайте.\n\n" .
                "Указанный: {$phoneData['phone']}\n" .
                "Ваш Telegram: {$contact['phone_number']}\n\n" .
                "Вернитесь на сайт и введите правильный номер."
            );
            return;
        }

        // Phone verified! Create or find user
        $user = User::firstOrCreate(
            ['phone' => $expectedPhone],
            [
                'name' => $contact['first_name'] . ' ' . ($contact['last_name'] ?? ''),
                'email' => null,
                'telegram_id' => $contact['user_id'] ?? null,
            ]
        );

        // Update telegram_id if not set
        if (!$user->telegram_id && isset($contact['user_id'])) {
            $user->telegram_id = $contact['user_id'];
            $user->save();
        }

        // Mark auth as successful
        Cache::put("tg_auth:{$authToken}", [
            'phone' => $expectedPhone,
            'user_id' => $user->id,
            'status' => 'success',
        ], now()->addMinutes(5));

        // Clean up chat mapping
        Cache::forget("tg_chat:{$authToken}");

        // Send success message
        $this->bot->sendSuccessMessage($chatId,
            "✅ Авторизация успешна!\n\n" .
            "Добро пожаловать, <b>{$user->name}</b>!\n\n" .
            "Теперь вернитесь на сайт — вы уже авторизованы."
        );
    }

    /**
     * Find auth token by chat_id (reverse lookup)
     */
    protected function findTokenByChatId(int $chatId): ?string
    {
        // Direct lookup - we store chat_id -> token mapping
        $token = Cache::get("tg_chat_token:{$chatId}");
        
        if ($token) {
            return $token;
        }
        
        // Fallback: scan known tokens
        $keys = Cache::get('tg_auth_keys', []);
        
        foreach ($keys as $token) {
            $storedChatId = Cache::get("tg_chat:{$token}");
            if ($storedChatId == $chatId) {
                return $token;
            }
        }
        
        return null;
    }

    /**
     * Normalize phone number to consistent format
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-digits
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle Russian numbers: 8 -> 7
        if (strlen($phone) === 11 && str_starts_with($phone, '8')) {
            $phone = '7' . substr($phone, 1);
        }
        
        // Add country code if missing (assume Russia)
        if (strlen($phone) === 10) {
            $phone = '7' . $phone;
        }
        
        return $phone;
    }

    /**
     * Set webhook URL (call manually or from artisan command)
     */
    public function setWebhook(Request $request)
    {
        $url = $request->input('url') ?? route('telegram.webhook');
        
        $result = $this->bot->setWebhook($url);
        
        return response()->json($result);
    }
}
