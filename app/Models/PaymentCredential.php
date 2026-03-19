<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'environment',
        'merchant_id',
        'secret_key',
        'callback_url',
        'is_active',
    ];
}
