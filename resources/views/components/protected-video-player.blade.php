{{--
    Protected Video Player Component
    
    Supports:
    - Kinescope (with DRM)
    - Yandex.Disk (with proxy protection)
    - YouTube (embed)
    - Direct URLs (with protection overlay)
    
    Protection features:
    - Disabled right-click
    - Disabled download controls
    - Dynamic watermark with user info
    - CSS overlay to prevent drag
--}}

@props([
    'lesson',
    'user' => null,
])

@php
    $videoSource = $lesson->video_source ?? 'auto';
    $videoUrl = $lesson->video_url;
    $embedUrl = $lesson->embed_video_url;
    $user = $user ?? auth()->user();
    
    // Determine actual video source
    if ($videoSource === 'auto' && $videoUrl) {
        if (str_contains($videoUrl, 'kinescope.io')) {
            $videoSource = 'kinescope';
        } elseif (str_contains($videoUrl, 'disk.yandex')) {
            $videoSource = 'yandex_disk';
        } elseif (str_contains($videoUrl, 'youtube.com') || str_contains($videoUrl, 'youtu.be')) {
            $videoSource = 'youtube';
        } elseif (str_contains($videoUrl, 'vimeo.com')) {
            $videoSource = 'vimeo';
        } else {
            $videoSource = 'direct';
        }
    }
    
    // Generate watermark text
    $watermarkText = '';
    if ($user) {
        $watermarkText = $user->email ?? $user->phone ?? "ID: {$user->id}";
    }
@endphp

<div class="protected-video-container" 
     id="video-container-{{ $lesson->id }}"
     x-data="protectedVideoPlayer({{ $lesson->id }}, '{{ $videoSource }}')"
     x-init="init()"
     @contextmenu.prevent
     @dragstart.prevent
     @selectstart.prevent>
    
    {{-- Watermark Overlay --}}
    @if($user)
        <div class="video-watermark" aria-hidden="true">
            <span>{{ $watermarkText }}</span>
        </div>
    @endif
    
    {{-- Video Player based on source --}}
    @switch($videoSource)
        @case('kinescope')
            {{-- Kinescope - already has DRM protection --}}
            <div class="video-wrapper">
                <iframe 
                    src="{{ $embedUrl }}"
                    class="video-iframe"
                    allow="autoplay; fullscreen; picture-in-picture; encrypted-media;"
                    allowfullscreen
                    loading="lazy">
                </iframe>
            </div>
            @break
            
        @case('yandex_disk')
            {{-- Yandex.Disk - use our protected proxy --}}
            <div class="video-wrapper">
                <video 
                    id="video-player-{{ $lesson->id }}"
                    class="video-player"
                    controls
                    controlsList="nodownload noplaybackrate"
                    disablePictureInPicture
                    x-ref="videoPlayer"
                    @loadeddata="onVideoLoaded()">
                    <source :src="streamUrl" type="video/mp4">
                    Ваш браузер не поддерживает видео.
                </video>
                
                {{-- Loading indicator --}}
                <div class="video-loading" x-show="loading">
                    <div class="loading-spinner"></div>
                    <span>Загрузка видео...</span>
                </div>
            </div>
            @break
            
        @case('youtube')
        @case('vimeo')
            {{-- External embeds --}}
            <div class="video-wrapper">
                <iframe 
                    src="{{ $embedUrl }}"
                    class="video-iframe"
                    allow="autoplay; fullscreen; picture-in-picture"
                    allowfullscreen
                    loading="lazy">
                </iframe>
            </div>
            @break
            
        @default
            {{-- Direct video URL --}}
            <div class="video-wrapper">
                <video 
                    id="video-player-{{ $lesson->id }}"
                    class="video-player"
                    controls
                    controlsList="nodownload noplaybackrate"
                    disablePictureInPicture
                    x-ref="videoPlayer">
                    <source src="{{ $embedUrl }}" type="video/mp4">
                    Ваш браузер не поддерживает видео.
                </video>
            </div>
    @endswitch
    
    {{-- Protection overlay (invisible but prevents some actions) --}}
    <div class="video-protection-overlay"></div>
</div>

<style>
    .protected-video-container {
        position: relative;
        width: 100%;
        background: #000;
        border-radius: 12px;
        overflow: hidden;
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }
    
    .video-wrapper {
        position: relative;
        width: 100%;
        padding-top: 56.25%; /* 16:9 aspect ratio */
    }
    
    .video-iframe,
    .video-player {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }
    
    .video-player {
        background: #000;
    }
    
    .video-watermark {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.15;
        font-size: 1.5rem;
        font-weight: 600;
        color: white;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        transform: rotate(-25deg);
        user-select: none;
        overflow: hidden;
    }
    
    .video-watermark span {
        white-space: nowrap;
    }
    
    .video-protection-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 80%;
        z-index: 5;
        pointer-events: none;
    }
    
    .video-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 20;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        color: white;
    }
    
    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* Disable video download on mobile */
    video::-webkit-media-controls-enclosure {
        overflow: hidden;
    }
    
    video::-webkit-media-controls-panel {
        width: calc(100% + 30px);
    }
    
    /* Hide download button in Chrome */
    video::-internal-media-controls-download-button {
        display: none;
    }
    
    video::-webkit-media-controls-download-button {
        display: none;
    }
</style>

<script>
    function protectedVideoPlayer(lessonId, source) {
        return {
            lessonId: lessonId,
            source: source,
            streamUrl: '',
            loading: true,
            
            init() {
                // Prevent right-click on video
                this.$el.addEventListener('contextmenu', e => e.preventDefault());
                
                // Load signed URL for Yandex.Disk videos
                if (this.source === 'yandex_disk') {
                    this.loadSignedUrl();
                }
                
                // Disable keyboard shortcuts for download
                document.addEventListener('keydown', this.blockDownloadKeys.bind(this));
                
                // Detect DevTools (basic detection)
                this.detectDevTools();
            },
            
            async loadSignedUrl() {
                try {
                    const response = await fetch(`/video/${this.lessonId}/signed-url`, {
                        credentials: 'same-origin',
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        this.streamUrl = data.url;
                    } else {
                        console.error('Failed to get video URL');
                    }
                } catch (e) {
                    // Fallback to default stream URL
                    this.streamUrl = `/video/${this.lessonId}/stream`;
                }
            },
            
            onVideoLoaded() {
                this.loading = false;
            },
            
            blockDownloadKeys(e) {
                // Block Ctrl+S, Ctrl+Shift+I, F12
                if ((e.ctrlKey && e.key === 's') || 
                    (e.ctrlKey && e.shiftKey && e.key === 'i') ||
                    e.key === 'F12') {
                    e.preventDefault();
                }
            },
            
            detectDevTools() {
                // Note: This is just a deterrent, not real security
                let devtools = false;
                const threshold = 160;
                
                setInterval(() => {
                    if (window.outerHeight - window.innerHeight > threshold || 
                        window.outerWidth - window.innerWidth > threshold) {
                        if (!devtools) {
                            devtools = true;
                            // Could log this or show a warning
                        }
                    } else {
                        devtools = false;
                    }
                }, 1000);
            }
        };
    }
</script>
