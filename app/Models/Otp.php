<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['identity', 'otp', 'type', 'expires_at', 'retries', 'verified'])]
class Otp extends Model
{
    //
}
