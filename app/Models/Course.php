<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'price',
        'is_published',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_published' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get all modules for this course
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('sort_order');
    }

    /**
     * Get published modules only
     */
    public function publishedModules(): HasMany
    {
        return $this->modules()->where('is_published', true);
    }

    /**
     * Get all enrollments for this course
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Scope to get only published courses
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Check if course is free
     */
    public function isFree(): bool
    {
        return empty($this->price) || $this->price <= 0;
    }

    /**
     * Get the formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->isFree()) {
            return 'Бесплатно';
        }
        return number_format($this->price, 0, ',', ' ') . ' ₽';
    }

    /**
     * Get total lessons count
     */
    public function getTotalLessonsCountAttribute(): int
    {
        return $this->lessons()->count();
    }

    /**
     * Get total modules count
     */
    public function getTotalModulesCountAttribute(): int
    {
        return $this->modules()->count();
    }
}
