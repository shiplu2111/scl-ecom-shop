<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'environment',
        'api_key',
        'api_secret',
        'account_id',
        'is_active',
    ];
}
