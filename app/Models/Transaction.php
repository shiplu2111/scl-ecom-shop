<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['order_id', 'gateway', 'transaction_id', 'amount', 'status', 'response_payload'])]
class Transaction extends Model
{
    protected $casts = [
        'response_payload' => 'array',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
