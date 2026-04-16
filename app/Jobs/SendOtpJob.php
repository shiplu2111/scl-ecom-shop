<?php

namespace App\Jobs;

use App\Models\Otp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOtpJob implements ShouldQueue
{
    use Queueable;

    public Otp $otpRecord;

    public function __construct(Otp $otpRecord)
    {
        $this->otpRecord = $otpRecord;
    }

    public function handle(\App\Services\Sms\SmsService $smsService, \App\Services\MailConfigService $mailConfig): void
    {
        $identity = $this->otpRecord->identity;
        $otp = $this->otpRecord->otp;

        if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
            try {
                // Apply database SMTP settings dynamically
                $mailConfig->apply();
                
                \Illuminate\Support\Facades\Mail::to($identity)->send(new \App\Mail\OtpMail($otp));
                
                Log::info("OTP email successfully dispatched to [{$identity}]");
            } catch (\Throwable $e) {
                Log::error("Failed to deliver OTP to [{$identity}]: " . $e->getMessage(), [
                    'exception' => $e->getTraceAsString()
                ]);
            }
        } else {
            $message = "Your OTP for registration is: {$otp}. Please do not share it with anyone.";
            $smsService->send($identity, $message);
        }
    }
}
