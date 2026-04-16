<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'purchase_id',
        'type',
        'quantity',
        'reference',
        'created_by'
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'sku', 'sku');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
