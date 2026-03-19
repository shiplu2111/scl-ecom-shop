<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMetadata extends Model
{
    protected $fillable = [
        'seoable_id',
        'seoable_type',
        'meta_title',
        'meta_description'
    ];

    public function seoable()
    {
        return $this->morphTo();
    }
}
