<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\VideoStreamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VideoStreamController extends Controller
{
    public function __construct(
        private VideoStreamService $videoService
    ) {}

    /**
     * Stream video from Yandex Disk (proxy with protection)
     */
    public function stream(Request $request, Lesson $lesson)
    {
        // Check user auth
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // Verify signed URL if provided
        $expires = $request->query('expires');
        $signature = $request->query('signature');
        
        if ($expires && $signature) {
            if (!$this->videoService->verifySignature($lesson->id, $user->id, (int)$expires, $signature)) {
                abort(403, 'Invalid or expired video link');
            }
        }

        // Get course through modules
        $module = $lesson->modules()->first();
        if (!$module) {
            abort(404, 'Lesson not attached to any module');
        }
        
        $course = $module->course;
        if (!$course || !$user->hasCourseAccess($course->id)) {
            abort(403, 'Access denied');
        }

        // Check video source
        if (empty($lesson->video_url) && empty($lesson->video_path)) {
            abort(404, 'Video not found');
        }

        // Get the video URL (from Yandex.Disk or direct)
        $videoUrl = $lesson->video_path ?: $lesson->video_url;
        
        // If Yandex.Disk link, get download URL
        if (str_contains($videoUrl, 'disk.yandex') || $lesson->video_source === 'yandex_disk') {
            $downloadUrl = $this->videoService->getYandexDiskDownloadUrl($videoUrl);
            
            if (!$downloadUrl) {
                abort(404, 'Could not get video URL from Yandex.Disk');
            }
        } else {
            $downloadUrl = $videoUrl;
        }

        // Get file info
        try {
            $headResponse = Http::head($downloadUrl);
            $mimeType = $headResponse->header('Content-Type') ?? 'video/mp4';
            $fileSize = (int) $headResponse->header('Content-Length');
        } catch (\Exception $e) {
            $mimeType = 'video/mp4';
            $fileSize = null;
        }

        // Handle range requests for video seeking
        $range = $request->header('Range');
        $start = 0;
        $end = $fileSize ? $fileSize - 1 : null;
        $statusCode = 200;

        if ($range && $fileSize) {
            preg_match('/bytes=(\d+)-(\d*)/', $range, $matches);
            $start = (int) $matches[1];
            $end = !empty($matches[2]) ? (int) $matches[2] : $fileSize - 1;
            $statusCode = 206;
        }

        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, no-store, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
            // Prevent embedding in other sites
            'X-Frame-Options' => 'SAMEORIGIN',
            'Content-Security-Policy' => "frame-ancestors 'self'",
        ];

        if ($fileSize) {
            $length = $end - $start + 1;
            $headers['Content-Length'] = $length;
            
            if ($statusCode === 206) {
                $headers['Content-Range'] = "bytes {$start}-{$end}/{$fileSize}";
            }
        }

        // Stream the video
        return new StreamedResponse(function () use ($downloadUrl, $start, $end) {
            $streamHeaders = [];
            
            if ($start > 0 || $end !== null) {
                $streamHeaders['Range'] = "bytes={$start}-" . ($end !== null ? $end : '');
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $downloadUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) {
                echo $data;
                flush();
                return strlen($data);
            });
            
            if (!empty($streamHeaders['Range'])) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Range: {$streamHeaders['Range']}"]);
            }
            
            curl_exec($ch);
            curl_close($ch);
        }, $statusCode, $headers);
    }

    /**
     * Get signed video URL for the player
     */
    public function getSignedUrl(Request $request, Lesson $lesson)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check access
        $module = $lesson->modules()->first();
        if (!$module) {
            return response()->json(['error' => 'Lesson not found'], 404);
        }
        
        $course = $module->course;
        if (!$course || !$user->hasCourseAccess($course->id)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $signedUrl = $this->videoService->generateSignedUrl($lesson->id, $user->id);

        return response()->json([
            'url' => $signedUrl,
            'expires_in' => 30 * 60, // 30 minutes
        ]);
    }
}
