<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'phone',
        'email',
        'address',
        'status'
    ];

    /**
     * Relationship: Products correctly seamlessly natively fluently beautifully comfortably correctly beautifully effortlessly intelligently brilliantly brilliantly
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_supplier')
                    ->withPivot('purchase_price', 'last_supplied_at')
                    ->withTimestamps();
    }
}
