<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'order_id', 
        'draft_order_id', 
        'user_id', 
        'gateway', 
        'transaction_id', 
        'type', 
        'description', 
        'amount', 
        'status', 
        'response_payload'
    ];

    protected $casts = [
        'response_payload' => 'array',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function draftOrder()
    {
        return $this->belongsTo(DraftOrder::class);
    }
}
