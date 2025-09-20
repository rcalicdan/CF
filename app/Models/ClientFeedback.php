<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ClientFeedback extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'client_feedback';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'opinion',
        'rating',
        'is_featured'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_featured' => 'boolean',
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByRating(Builder $query, int $rating): Builder
    {
        return $query->where('rating', $rating);
    }

    public function scopeHighRating(Builder $query): Builder
    {
        return $query->whereIn('rating', [4, 5]);
    }

    public function scopeLowRating(Builder $query): Builder
    {
        return $query->whereIn('rating', [1, 2]);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    public function getRatingTextAttribute(): string
    {
        $ratings = [
            1 => 'Poor',
            2 => 'Fair', 
            3 => 'Good',
            4 => 'Very Good',
            5 => 'Excellent'
        ];

        return $ratings[$this->rating] ?? 'Not Rated';
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('M d, Y \a\t g:i A');
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = ucwords(strtolower(trim($value)));
    }

    public function toggleFeatured(): bool
    {
        return $this->update(['is_featured' => !$this->is_featured]);
    }

    public function isPositive(): bool
    {
        return $this->rating >= 4;
    }

    public function isNegative(): bool
    {
        return $this->rating <= 2;
    }

    public static function getAverageRating(): float
    {
        return static::avg('rating') ?? 0;
    }

    public static function getStatistics(): array
    {
        $total = static::count();
        $average = static::getAverageRating();
        
        return [
            'total' => $total,
            'average_rating' => round($average, 2),
            'recent_count' => static::recent()->count(),
            'featured_count' => static::featured()->count(),
        ];
    }
}