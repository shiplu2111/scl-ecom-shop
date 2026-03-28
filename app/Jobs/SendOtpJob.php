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

    public function handle(\App\Services\Sms\SmsService $smsService): void
    {
        $identity = $this->otpRecord->identity;
        $otp = $this->otpRecord->otp;

        if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
            // Placeholder: Dispatch standard Mailable for email
            // Mail::to($identity)->send(new OtpMail($otp));
            Log::info("DISPATCHING OTP EMAIL TO [{$identity}]: {$otp}");
        } else {
            $message = "Your OTP for registration is: {$otp}. Please do not share it with anyone.";
            $smsService->send($identity, $message);
        }
    }
}
