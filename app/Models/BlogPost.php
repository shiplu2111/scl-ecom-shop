<?php

namespace App\Models;

use App\Traits\Seoable;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use Seoable;
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'author_id',
        'image_path',
        'status'
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }
        
        // Force use the root APP_URL from .env to avoid /API/V1/ prefixing
        return rtrim(config('app.url'), '/') . '/storage/' . $this->image_path;
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories()
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_category_post', 'blog_post_id', 'blog_category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag', 'blog_post_id', 'blog_tag_id');
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class, 'blog_post_id');
    }
}
