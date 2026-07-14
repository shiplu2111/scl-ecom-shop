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
        // Keep plain text - Laravel 'encrypted' cast breaks after APP_KEY rotate
        // and produces "The payload is invalid" during payment initiation.
        'is_active' => 'boolean',
    ];

    /**
     * Safely resolve the stored API key (supports legacy encrypted values).
     */
    public function getResolvedSecretKey(): string
    {
        $raw = $this->attributes['secret_key'] ?? null;

        if (!is_string($raw) || trim($raw) === '' || $raw === '********') {
            throw new \RuntimeException(
                'Payment API key is missing. Please re-save the Full API Key in Admin → Payment settings.'
            );
        }

        // Legacy Laravel encrypted cast payload
        $looksEncrypted = str_starts_with($raw, 'eyJpdiI6')
            || (str_starts_with($raw, '{') && str_contains($raw, '"iv"'));

        if ($looksEncrypted) {
            try {
                $decrypted = decrypt($raw);
                if (is_string($decrypted) && trim($decrypted) !== '') {
                    return $decrypted;
                }
            } catch (\Throwable $e) {
                throw new \RuntimeException(
                    'Payment API key cannot be decrypted (APP_KEY mismatch). Please re-save the Full API Key in Admin → Payment settings.'
                );
            }
        }

        return $raw;
    }

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
