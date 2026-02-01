<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'video_url',
        'video_source',
        'video_path',
        'sort_order',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all modules this lesson belongs to (many-to-many)
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'lesson_module')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('lesson_module.sort_order');
    }

    /**
     * Get all courses this lesson appears in (through modules)
     */
    public function courses()
    {
        return $this->modules->map(fn ($m) => $m->course)->unique('id');
    }

    /**
     * Get assignment for this lesson
     */
    public function assignment(): HasOne
    {
        return $this->hasOne(Assignment::class);
    }

    /**
     * Get lesson progress for users
     */
    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * Scope to get only published lessons
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope to order lessons by sort_order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Check if this lesson has video content
     */
    public function hasVideo(): bool
    {
        return !empty($this->video_url) || !empty($this->video_path);
    }

    /**
     * Check if this lesson has an assignment
     */
    public function hasAssignment(): bool
    {
        return $this->assignment()->exists();
    }

    /**
     * Get embedded video URL (convert YouTube/Kinescope links to embed format)
     */
    public function getEmbedVideoUrlAttribute(): ?string
    {
        // If using Yandex Disk, return the stream URL
        if ($this->video_source === 'yandex_disk' && $this->video_path) {
            return route('video.stream', ['lesson' => $this->id]);
        }

        if (!$this->video_url) {
            return null;
        }

        $url = $this->video_url;

        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return "https://www.youtube.com/embed/{$matches[1]}";
        }

        // Kinescope
        if (preg_match('/kinescope\.io\/([a-zA-Z0-9]+)/', $url, $matches)) {
            return "https://kinescope.io/embed/{$matches[1]}";
        }

        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return "https://player.vimeo.com/video/{$matches[1]}";
        }

        // VK Video
        if (preg_match('/vk\.com\/video(-?\d+)_(\d+)/', $url, $matches)) {
            return "https://vk.com/video_ext.php?oid={$matches[1]}&id={$matches[2]}";
        }

        // Return as-is if already an embed URL or unknown format
        return $url;
    }

    /**
     * Get modules count for display
     */
    public function getModulesCountAttribute(): int
    {
        return $this->modules()->count();
    }
}
