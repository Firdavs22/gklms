<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexDiskService
{
    private string $token;
    private string $baseUrl = 'https://cloud-api.yandex.net/v1/disk';
    private string $baseFolder;
    private int $cacheTtl;

    public function __construct()
    {
        $this->token = config('yandex-disk.oauth_token', '');
        $this->baseFolder = config('yandex-disk.base_folder', '/LMS Videos');
        $this->cacheTtl = config('yandex-disk.cache_ttl', 3600);
    }

    /**
     * Check if Yandex Disk is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->token);
    }

    /**
     * Upload a file to Yandex Disk
     */
    public function upload(UploadedFile $file, string $folder = ''): ?string
    {
        if (!$this->isConfigured()) {
            Log::error('YandexDisk: OAuth token not configured');
            return null;
        }

        $path = rtrim($this->baseFolder, '/') . '/' . ltrim($folder, '/');
        $filename = time() . '_' . $file->getClientOriginalName();
        $fullPath = $path . '/' . $filename;

        // Ensure folder exists
        $this->createFolder($path);

        // Get upload URL
        $response = Http::withToken($this->token)
            ->get("{$this->baseUrl}/resources/upload", [
                'path' => $fullPath,
                'overwrite' => true,
            ]);

        if (!$response->successful()) {
            Log::error('YandexDisk upload URL error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $uploadUrl = $response->json('href');
        
        if (!$uploadUrl) {
            Log::error('YandexDisk: No upload URL in response');
            return null;
        }

        // Upload file
        $uploadResponse = Http::attach('file', $file->get(), $filename)
            ->put($uploadUrl);

        if (!$uploadResponse->successful()) {
            Log::error('YandexDisk file upload error', [
                'status' => $uploadResponse->status(),
            ]);
            return null;
        }

        return $fullPath;
    }

    /**
     * Get temporary download URL for a file
     */
    public function getDownloadUrl(string $path): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        // Check cache first
        $cacheKey = 'yadisk_download_' . md5($path);
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($path) {
            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/resources/download", [
                    'path' => $path,
                ]);

            if (!$response->successful()) {
                Log::error('YandexDisk download URL error', [
                    'path' => $path,
                    'status' => $response->status(),
                ]);
                return null;
            }

            return $response->json('href');
        });
    }

    /**
     * Delete a file from Yandex Disk
     */
    public function delete(string $path): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $response = Http::withToken($this->token)
            ->delete("{$this->baseUrl}/resources", [
                'path' => $path,
                'permanently' => false,
            ]);

        return $response->successful() || $response->status() === 204;
    }

    /**
     * Create folder on Yandex Disk
     */
    private function createFolder(string $path): void
    {
        // Split path into parts and create each level
        $parts = explode('/', trim($path, '/'));
        $currentPath = '';

        foreach ($parts as $part) {
            if (empty($part)) continue;
            
            $currentPath .= '/' . $part;
            
            Http::withToken($this->token)
                ->put("{$this->baseUrl}/resources", [
                    'path' => $currentPath,
                ]);
        }
    }

    /**
     * Get file info
     */
    public function getFileInfo(string $path): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $response = Http::withToken($this->token)
            ->get("{$this->baseUrl}/resources", [
                'path' => $path,
            ]);

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Check if file exists
     */
    public function exists(string $path): bool
    {
        return $this->getFileInfo($path) !== null;
    }
}
