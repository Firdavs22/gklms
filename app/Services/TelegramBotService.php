<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    protected string $token;
    protected string $botUsername;
    protected string $apiUrl;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
        $this->botUsername = config('services.telegram.bot_username');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";
    }

    /**
     * Send a message to a chat
     */
    public function sendMessage(int|string $chatId, string $text, array $replyMarkup = null): array
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }

        return $this->request('sendMessage', $params);
    }

    /**
     * Send message with "Share Contact" button
     */
    public function requestContact(int|string $chatId, string $text): array
    {
        $keyboard = [
            'keyboard' => [
                [
                    [
                        'text' => '📱 Поделиться номером телефона',
                        'request_contact' => true,
                    ]
                ]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ];

        return $this->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Remove keyboard after successful auth
     */
    public function sendSuccessMessage(int|string $chatId, string $text): array
    {
        $keyboard = [
            'remove_keyboard' => true,
        ];

        return $this->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Get bot deep link with start parameter
     */
    public function getDeepLink(string $token): string
    {
        return "https://t.me/{$this->botUsername}?start={$token}";
    }

    /**
     * Set webhook URL
     */
    public function setWebhook(string $url): array
    {
        return $this->request('setWebhook', [
            'url' => $url,
            'allowed_updates' => ['message'],
        ]);
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(): array
    {
        return $this->request('deleteWebhook');
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): array
    {
        return $this->request('getWebhookInfo');
    }

    /**
     * Make API request to Telegram
     */
    protected function request(string $method, array $params = []): array
    {
        try {
            $response = Http::timeout(30)->post("{$this->apiUrl}/{$method}", $params);
            
            $data = $response->json();
            
            if (!$response->successful() || !($data['ok'] ?? false)) {
                Log::error("Telegram API error", [
                    'method' => $method,
                    'response' => $data,
                ]);
            }
            
            return $data;
        } catch (\Exception $e) {
            Log::error("Telegram API exception: " . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
