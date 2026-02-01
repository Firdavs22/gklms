<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowTelegramIframe
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Remove X-Frame-Options header to allow viewing inside Telegram Web App iframe
        if (method_exists($response, 'header')) {
            $response->header('X-Frame-Options', 'ALLOW-FROM https://t.me');
            // Alternatively, remove it completely for better compatibility with Telegram
            $response->headers->remove('X-Frame-Options');
        }

        return $response;
    }
}
