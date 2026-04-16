<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_id', 'image_path', 'is_thumbnail'])]
class ProductImage extends Model
{
    protected $appends = ['url'];

    protected $casts = [
        'is_thumbnail' => 'boolean',
    ];

    public function getUrlAttribute()
    {
        if (!$this->image_path) return null;
        return asset('storage/' . $this->image_path);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
