<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Repositories\OtpRepositoryInterface;
use App\Mail\AdminResetPasswordMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\BaseService;

class AdminPasswordService extends BaseService
{
    protected OtpRepositoryInterface $otpRepository;

    public function __construct(OtpRepositoryInterface $otpRepository)
    {
        $this->otpRepository = $otpRepository;
    }

    public function sendForgetPasswordOtp(string $email)
    {
        $admin = Admin::where('email', $email)->first();
        if (!$admin) return false;

        $this->otpRepository->invalidateOldTokens($email, 'admin_password_reset');

        $otpCode = (string) rand(100000, 999999);
        $this->otpRepository->create([
            'identity'   => $email,
            'otp'        => $otpCode,
            'type'       => 'admin_password_reset',
            'expires_at' => now()->addMinutes(10),
            'verified'   => false,
        ]);

        Mail::to($email)->send(new AdminResetPasswordMail($otpCode));

        return true;
    }

    public function resetPassword(array $data)
    {
        $otpRecord = $this->otpRepository->findValidOtp($data['email'], $data['otp'], 'admin_password_reset');
        if (!$otpRecord) return false;

        $admin = Admin::where('email', $data['email'])->first();
        if (!$admin) return false;

        $admin->update(['password' => Hash::make($data['password'])]);
        $this->otpRepository->markAsVerified($otpRecord->id);

        return true;
    }

    public function changePassword(Admin $admin, array $data)
    {
        if (!Hash::check($data['current_password'], $admin->password)) {
            return ['status' => false, 'message' => 'Current password does not match'];
        }

        $admin->update(['password' => Hash::make($data['new_password'])]);

        return ['status' => true, 'message' => 'Password updated successfully'];
    }
}
