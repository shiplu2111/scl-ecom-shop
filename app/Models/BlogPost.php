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
        'blog_category_id',
        'image_path',
        'status'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(BlogTag::class);
    }
}
