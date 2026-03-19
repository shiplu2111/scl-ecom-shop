<?php

namespace App\Services;

use App\Jobs\SendOtpJob;
use App\Repositories\OtpRepositoryInterface;
use Illuminate\Validation\ValidationException;

class OtpService extends BaseService
{
    protected OtpRepositoryInterface $otpRepo;
    protected int $expiryMinutes = 5;
    protected int $maxRetries = 3;

    public function __construct(OtpRepositoryInterface $otpRepo)
    {
        $this->otpRepo = $otpRepo;
    }

    public function generateAndSend(string $identity, string $type)
    {
        // Prevent abuse: Check if an active OTP was sent less than 1 minute ago
        $latest = $this->otpRepo->findLatestActive($identity, $type);
        if ($latest && $latest->created_at->diffInSeconds(now()) < 60) {
            throw ValidationException::withMessages(['otp' => ['Please wait before requesting a new OTP.']]);
        }

        // Invalidate older tokens for security
        $this->otpRepo->invalidateOldTokens($identity, $type);

        // Generate 6-digit OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $otpRecord = $this->otpRepo->create([
            'identity' => $identity,
            'otp'      => $otpCode,
            'type'     => $type,
            'expires_at' => now()->addMinutes($this->expiryMinutes),
            'retries'  => 0,
            'verified' => false
        ]);

        // Dispatch Job
        SendOtpJob::dispatch($otpRecord);

        return true;
    }

    public function verify(string $identity, string $otp, string $type)
    {
        $otpRecord = $this->otpRepo->findLatestActive($identity, $type);

        if (!$otpRecord) {
            throw ValidationException::withMessages(['otp' => ['No active OTP found or it has expired.']]);
        }

        if ($otpRecord->retries >= $this->maxRetries) {
            $this->otpRepo->invalidateOldTokens($identity, $type);
            throw ValidationException::withMessages(['otp' => ['Maximum retry limit exceeded. Please request a new OTP.']]);
        }

        if ($otpRecord->otp !== $otp) {
            $this->otpRepo->incrementRetry($otpRecord->id);
            throw ValidationException::withMessages(['otp' => ['Invalid OTP provided.']]);
        }

        $this->otpRepo->markAsVerified($otpRecord->id);

        return true;
    }

    public function isVerified(string $identity, string $type)
    {
        // Simple check to see if there is recently verified OTP logic (within the valid registration timeframe, e.g 15 minus overall)
        $record = \App\Models\Otp::where('identity', $identity)
            ->where('type', $type)
            ->where('verified', true)
            ->where('updated_at', '>=', now()->subMinutes(15))
            ->first();
            
        return $record !== null;
    }
}
