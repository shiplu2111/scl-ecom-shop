<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'image',
        'title',
        'subtitle',
        'button_text',
        'button_link',
        'status',
        'order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer',
    ];

    protected $appends = ['image_url'];

    /**
     * Get the full URL for the banner image.
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;
        
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }
}
