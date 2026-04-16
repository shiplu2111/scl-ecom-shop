<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_no',
        'type',
        'adjustable_id',
        'adjustable_type',
        'reason',
        'notes',
        'total_amount',
        'created_by',
    ];

    public function adjustable()
    {
        return $this->morphTo();
    }

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
