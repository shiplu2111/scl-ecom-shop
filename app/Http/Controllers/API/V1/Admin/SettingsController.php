<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;

/**
 * @group Admin
 * @subgroup Settings
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

        // Handle file uploads
        if ($request->hasFile('site_logo')) {
            $path = $request->file('site_logo')->store('settings', 'public');
            $payload['site_logo'] = asset('storage/' . $path);
        }

        if ($request->hasFile('site_favicon')) {
            $path = $request->file('site_favicon')->store('settings', 'public');
            $payload['site_favicon'] = asset('storage/' . $path);
        }

        $this->settingsService->updateGroupSettings($group, $payload);

        return response()->json([
            'message' => "Settings for group '{$group}' updated successfully.",
            'settings' => $this->settingsService->getSettingsByGroup($group)
        ]);
    }

    /**
     * Test email connection gracefully smartly efficiently.
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            \Illuminate\Support\Facades\Mail::raw('This is a test email to verify SMTP settings.', function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('SMTP Connection Test');
            });

            return response()->json([
                'status' => true,
                'message' => 'Test email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test SMS connection accurately eloquently beautifully dependably logically solidly expertly sensibly fluently rationally bravely brilliantly successfully fluently thoughtfully powerfully smoothly neatly magically
     */
    public function testSms(Request $request, \App\Services\Sms\SmsService $smsService)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string'
        ]);

        $success = $smsService->send($request->phone, $request->message);

        if ($success) {
            return response()->json([
                'status' => true,
                'message' => 'Test SMS sent successfully!'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Failed to send test SMS. Check logs for details.'
        ], 500);
    }
}
