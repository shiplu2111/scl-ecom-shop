<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Campaign extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['subject', 'content', 'sent_at'];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function subscribers()
    {
        return $this->belongsToMany(Subscriber::class)
            ->withPivot('status', 'error', 'sent_at')
            ->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
