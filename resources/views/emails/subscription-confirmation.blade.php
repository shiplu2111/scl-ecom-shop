@extends('emails.layout')

@section('content')
    <h2 style="margin: 0 0 10px; font-size: 22px; font-weight: 700;">Subscription Confirmed</h2>
    <p>Hello,</p>
    <p>Thank you for subscribing to our newsletter! You'll now receive the latest updates, exclusive offers, and news from {{ $site_settings['site_name'] }} directly in your inbox (<strong>{{ $email }}</strong>).</p>
    
    <div style="margin: 30px 0; text-align: center;">
        <a href="{{ url('/shop') }}" style="display: inline-block; padding: 14px 28px; background-color: #000000; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 700; transition: background 0.2s;">
            Explore Our Products
        </a>
    </div>
    
    <p style="margin: 20px 0 10px;">If you didn't mean to subscribe, you can safely ignore this email or unsubscribe at any time from your account settings.</p>
    
    <hr style="border: 0; border-top: 1px solid #edf2f7; margin: 30px 0;">
    
    <p style="font-size: 14px; text-align: center; color: #64748b;">
        Welcome to the community! We're glad to have you.
    </p>
@endsection
