<?php

namespace App\Repositories;

use App\Models\Otp;

class OtpRepository extends BaseRepository implements OtpRepositoryInterface
{
    public function __construct(Otp $model)
    {
        parent::__construct($model);
    }

    public function findValidOtp(string $identity, string $otp, string $type)
    {
        return $this->model->where('identity', $identity)
            ->where('otp', $otp)
            ->where('type', $type)
            ->where('verified', false)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function findLatestActive(string $identity, string $type)
    {
        return $this->model->where('identity', $identity)
            ->where('type', $type)
            ->where('verified', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    public function invalidateOldTokens(string $identity, string $type)
    {
        return $this->model->where('identity', $identity)
            ->where('type', $type)
            ->where('verified', false)
            ->update(['expires_at' => now()]);
    }

    public function incrementRetry(int $otpId)
    {
        return $this->model->where('id', $otpId)->increment('retries');
    }

    public function markAsVerified(int $otpId)
    {
        return $this->model->where('id', $otpId)->update(['verified' => true]);
    }
}
