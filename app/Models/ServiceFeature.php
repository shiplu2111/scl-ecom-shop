<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceFeature extends Model
{
    protected $fillable = ['icon', 'title', 'subtitle', 'sort_order'];
}
