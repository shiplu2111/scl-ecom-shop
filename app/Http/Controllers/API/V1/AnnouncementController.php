<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Traits\ApiResponseTrait;

class AnnouncementController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get active announcements for the website.
     */
    public function active()
    {
        $announcements = Announcement::where('is_active', true)
            ->latest()
            ->get();

        return $this->successResponse($announcements, 'Active announcements retrieved successfully');
    }
}
