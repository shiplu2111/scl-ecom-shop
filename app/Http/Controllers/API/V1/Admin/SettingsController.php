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

    const SAFE_GROUPS = ['standard', 'currency', 'branding', 'footer', 'cookie'];

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Get settings dependably expertly intelligently beautifully accurately eloquently beautifully dependably logically solidly expertly sensibly fluently rationally bravely brilliantly successfully fluently thoughtfully powerfully smoothly neatly magically
     */
    public function getByGroup(Request $request, string $group)
    {
        // If not a safe group, require manage_settings permission
        if (!in_array($group, self::SAFE_GROUPS) && !$request->user()->can('manage_settings')) {
            return response()->json([
                'status' => false,
                'message' => 'User does not have the right permissions to access sensitive settings.'
            ], 403);
        }

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
    public function testEmail(Request $request, \App\Services\MailConfigService $mailConfig)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            $settings = app(\App\Services\SettingsService::class)->getSettingsByGroup('email');

            if (empty($settings['mail_host']) || empty($settings['mail_username']) || empty($settings['mail_password'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'SMTP settings incomplete. Please save mail host, username and password first.',
                ], 422);
            }

            // Apply DB SMTP settings for this request
            $mailConfig->apply();

            $mailer = config('mail.default');
            $from = (string) config('mail.from.address');

            if ($mailer !== 'smtp') {
                return response()->json([
                    'status' => false,
                    'message' => 'Mailer is not set to SMTP. Clear cache and try again.',
                ], 422);
            }

            \Illuminate\Support\Facades\Mail::raw(
                "This is a test email to verify SMTP settings.\n\nSent at: " . now()->toDateTimeString(),
                function ($message) use ($request, $from) {
                    $message->to($request->email)
                        ->subject('SMTP Connection Test');

                    if ($from) {
                        $message->from($from, config('mail.from.name'));
                        $message->sender($from);
                    }

                    $replyTo = config('mail.reply_to_override');
                    if ($replyTo) {
                        $message->replyTo($replyTo);
                    }
                }
            );

            return response()->json([
                'status' => true,
                'message' => 'Test email sent successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
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
