<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Subscriber extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['email', 'is_active'];

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class)
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
