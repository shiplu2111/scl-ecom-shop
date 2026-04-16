<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'base_url',
        'environment',
        'secret_key',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'secret_key',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['secret_key_masked'];

    public function getSecretKeyMaskedAttribute()
    {
        return $this->attributes['secret_key'] ? '********' : null;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'secret_key' => 'encrypted',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model to handle single active config per gateway name.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->is_active) {
                // Deactivate other configs for the same gateway name
                static::where('id', '!=', $model->id)
                    ->where('name', $model->name)
                    ->update(['is_active' => false]);
            }
        });
    }
}
