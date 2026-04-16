@extends('emails.layout')

@section('content')
    <h2 style="margin: 0 0 10px; font-size: 22px; font-weight: 700;">Password Reset OTP</h2>
    <p>Hello,</p>
    <p>You are receiving this email because we received a password reset request for your administrative account at {{ $site_settings['site_name'] }}.</p>
    <p>Please use the following One-Time Password (OTP) to reset your password:</p>
    
    <div style="font-size: 36px; font-weight: 800; color: #2563eb; letter-spacing: 8px; margin: 30px 0; text-align: center; border: 2px dashed #e2e8f0; padding: 25px; border-radius: 12px; background: #f8fafc;">
        {{ $otp }}
    </div>
    
    <p style="margin: 30px 0 10px;">This OTP will expire in 10 minutes. If you did not request a password reset, no further action is required.</p>
    
    <hr style="border: 0; border-top: 1px solid #edf2f7; margin: 30px 0;">
    
    <p style="font-size: 14px; text-align: center; color: #64748b;">
        This is an automated security message, please do not reply directly to this email.
    </p>
@endsection
