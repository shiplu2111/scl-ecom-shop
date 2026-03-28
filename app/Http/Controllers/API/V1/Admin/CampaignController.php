<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Models\Campaign;
use Illuminate\Http\Request;

/**
 * @group Admin
 * @subgroup Campaign Management
 */
class CampaignController extends BaseController
{
    /**
     * List all campaigns.
     */
    public function index()
    {
        $campaigns = Campaign::withCount('subscribers')->latest()->get();
        return $this->successResponse($campaigns, 'Campaigns retrieved successfully');
    }

    /**
     * Show campaign details.
     */
    public function show(Campaign $campaign)
    {
        $campaign->load(['subscribers' => function ($query) {
            $query->select('subscribers.id', 'subscribers.email')
                  ->withPivot('status', 'error', 'sent_at');
        }]);
        
        return $this->successResponse($campaign, 'Campaign details retrieved successfully');
    }
}
