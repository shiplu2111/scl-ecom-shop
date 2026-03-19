<?php

namespace App\Logging;

use Spatie\Activitylog\Contracts\Activity;
use Illuminate\Support\Facades\Request;

class LogIpAddressTap
{
    public function __invoke(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->put('ip_address', Request::ip());
    }
}
