<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Traits\Seoable;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'logo'])]
class Brand extends Model
{
    use HasFactory, SoftDeletes, Seoable;
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
