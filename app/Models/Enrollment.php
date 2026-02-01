<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'payment_id',
        'amount_paid',
        'payment_provider',
        'enrolled_at',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'enrolled_at' => 'datetime',
    ];

    /**
     * Get the user who made this enrollment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course for this enrollment
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the formatted amount paid
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_paid, 0, ',', ' ') . ' ₽';
    }
}
