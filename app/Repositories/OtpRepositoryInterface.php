<?php

namespace App\Repositories;

interface OtpRepositoryInterface extends BaseRepositoryInterface
{
    public function findValidOtp(string $identity, string $otp, string $type);
    public function findLatestActive(string $identity, string $type);
    public function invalidateOldTokens(string $identity, string $type);
    public function incrementRetry(int $otpId);
    public function markAsVerified(int $otpId);
}
