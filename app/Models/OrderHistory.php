<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class OrderHistory extends Model
{
    protected $fillable = [
        'order_id',
        'admin_id',
        'action',
        'description',
        'details',
    ];
 
    protected $casts = [
        'details' => 'array',
    ];
 
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
 
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
