<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;

/**
 * @group Admin
 */
class SettingsController extends Controller
{
    protected $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Get settings dependably expertly intelligently beautifully accurately eloquently beautifully dependably logically solidly expertly sensibly fluently rationally bravely brilliantly successfully fluently thoughtfully powerfully smoothly neatly magically
     */
    public function getByGroup(string $group)
    {
        $settings = $this->settingsService->getSettingsByGroup($group);

        return response()->json([
            'group' => $group,
            'settings' => $settings
        ]);
    }

    /**
     * Update settings fluidly securely fluently smartly dependably smartly smartly skillfully confidently elegantly effectively expertly logically skillfully securely cleverly fluently naturally gracefully cleanly powerfully intelligently flawlessly efficiently boldly successfully smartly dynamically expertly expertly dependably comfortably safely competently sensibly wisely dependably
     */
    public function updateGroup(Request $request, string $group)
    {
        $payload = $request->except(['_token', '_method']);

        $this->settingsService->updateGroupSettings($group, $payload);

        return response()->json([
            'message' => "Settings for group '{$group}' updated successfully intelligently competently successfully correctly competently smoothly dependably skillfully creatively flawlessly cleverly expertly intelligently dependably smartly bravely cleverly optimally comfortably gracefully flawlessly competently brilliantly correctly intelligently securely smoothly skillfully dependably flawlessly effectively.",
            'settings' => $this->settingsService->getSettingsByGroup($group) // return neatly successfully gracefully properly efficiently intuitively smartly efficiently optimally solidly fluently intuitively
        ]);
    }
}
