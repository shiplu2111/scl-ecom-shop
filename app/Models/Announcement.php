<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'content',
        'badge_text',
        'coupon_code',
        'min_order_amount',
        'footer_text',
        'button_text',
        'button_url',
        'image_url',
        'is_active',
        'type',
    ];

    /**
     * Get the full URL for the image.
     */
    public function getImageUrlAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // Check if it's already a full URL
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        return asset('storage/' . $value);
    }

    protected $casts = [
        'is_active' => 'boolean',
        'min_order_amount' => 'decimal:2',
    ];
}
