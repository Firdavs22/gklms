<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
     * Web App entry point - redirects to dashboard
     */
    public function index(Request $request)
    {
        // If user is authenticated via middleware, redirect to dashboard
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        // Show a page that handles initData and authenticates via JS
        return view('webapp.index');
    }

    /**
     * API endpoint to authenticate from Web App
     */
    public function authenticate(Request $request)
    {
        $initData = $request->input('initData');
        
        if (!$initData) {
            return response()->json(['error' => 'No initData provided'], 400);
        }

        // The middleware will handle authentication
        $request->headers->set('X-Telegram-Init-Data', $initData);
        
        // Trigger middleware manually or redirect
        return response()->json([
            'authenticated' => auth()->check(),
            'user' => auth()->user() ? [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
            ] : null,
            'redirect' => route('dashboard'),
        ]);
    }
}
