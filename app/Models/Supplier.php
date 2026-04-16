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
     * Relationship: Inventories (SKU-based)
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
