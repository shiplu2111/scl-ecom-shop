@extends('vendor.installer.layout')

@section('header')
    <h1>Welcome to Installation</h1>
    <p>Thank you for choosing our application. This magic wizard will help you set up everything in just a few minutes.</p>
@endsection

@section('content')
    <div style="text-align: center; padding: 40px 0;">
        <div style="font-size: 4rem; margin-bottom: 20px;">🚀</div>
        <h2 style="margin-bottom: 10px;">Ready to start?</h2>
        <p style="color: var(--text-muted);">Before we begin, please make sure you have your database credentials handy. We'll check your server compatibility in the next step.</p>
    </div>
@endsection

@section('footer')
    <div></div>
    <a href="{{ route('install.requirements') }}" class="btn btn-primary">
        Start Installation
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 8px;"><path d="m9 18 6-6-6-6"/></svg>
    </a>
@endsection
