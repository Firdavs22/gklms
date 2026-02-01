<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VideoStreamService
{
    /**
     * Generate a signed URL for secure video streaming
     * URL expires after specified minutes
     */
    public function generateSignedUrl(int $lessonId, int $userId, int $expiresInMinutes = 30): string
    {
        $expires = now()->addMinutes($expiresInMinutes)->timestamp;
        $signature = $this->generateSignature($lessonId, $userId, $expires);
        
        return route('video.stream', [
            'lesson' => $lessonId,
            'expires' => $expires,
            'signature' => $signature,
        ]);
    }

    /**
     * Verify a signed URL
     */
    public function verifySignature(int $lessonId, int $userId, int $expires, string $signature): bool
    {
        // Check if expired
        if ($expires < now()->timestamp) {
            return false;
        }

        // Verify signature
        $expectedSignature = $this->generateSignature($lessonId, $userId, $expires);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Generate HMAC signature
     */
    protected function generateSignature(int $lessonId, int $userId, int $expires): string
    {
        $data = "{$lessonId}:{$userId}:{$expires}";
        $secret = config('app.key');
        
        return hash_hmac('sha256', $data, $secret);
    }

    /**
     * Get direct download URL from Yandex.Disk public link
     * 
     * @param string $publicUrl - Public link like https://disk.yandex.ru/i/xxxxx
     * @return string|null - Direct download URL
     */
    public function getYandexDiskDownloadUrl(string $publicUrl): ?string
    {
        // Cache the download URL for 1 hour (Yandex links can be slow to generate)
        $cacheKey = 'yandex_download_' . md5($publicUrl);
        
        return Cache::remember($cacheKey, 3600, function () use ($publicUrl) {
            try {
                // Yandex Disk API endpoint to get download link
                $apiUrl = 'https://cloud-api.yandex.net/v1/disk/public/resources/download';
                
                $response = Http::get($apiUrl, [
                    'public_key' => $publicUrl,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['href'] ?? null;
                }

                Log::error('Yandex Disk API error', [
                    'url' => $publicUrl,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('Yandex Disk API exception', [
                    'url' => $publicUrl,
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Get Kinescope embed URL with security token
     */
    public function getKinescopeEmbedUrl(string $videoId, ?int $userId = null): string
    {
        $baseUrl = "https://kinescope.io/embed/{$videoId}";
        
        $params = [
            'autoplay' => '0',
            'preload' => '1',
        ];

        // Add watermark with user info if available
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                // Kinescope supports watermarks via URL params or API
                $watermarkText = $user->email ?? $user->phone ?? "ID:{$userId}";
                $params['watermark'] = $watermarkText;
            }
        }

        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * Determine video source type from URL
     */
    public function detectVideoSource(string $url): string
    {
        if (str_contains($url, 'disk.yandex')) {
            return 'yandex_disk';
        }

        if (str_contains($url, 'kinescope.io')) {
            return 'kinescope';
        }

        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            return 'youtube';
        }

        if (str_contains($url, 'vimeo.com')) {
            return 'vimeo';
        }

        if (str_contains($url, 'vk.com/video')) {
            return 'vk';
        }

        return 'direct';
    }

    /**
     * Extract video ID from Kinescope URL
     */
    public function extractKinescopeId(string $url): ?string
    {
        if (preg_match('/kinescope\.io\/(?:embed\/)?([a-zA-Z0-9]+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
