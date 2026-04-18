@extends('vendor.installer.layout')

@section('header')
    <h1>Application Settings</h1>
    <p>Configure your application details. These settings will be stored in your .env file.</p>
@endsection

@section('content')
    <form id="env-form" action="{{ route('install.post.environment') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="app_name">Application Name</label>
            <input type="text" id="app_name" name="app_name" value="{{ old('app_name', config('app.name')) }}" placeholder="e.g. My Awesome Shop" required>
        </div>

        <div class="form-group">
            <label for="app_url">Backend API URL</label>
            <input type="url" id="app_url" name="app_url" value="{{ old('app_url', url('/')) }}" placeholder="https://api.myapp.com" required>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;">The base URL of this Laravel application.</p>
        </div>

        <div class="form-group">
            <label for="app_frontend_url">Frontend URL</label>
            <input type="url" id="app_frontend_url" name="app_frontend_url" value="{{ old('app_frontend_url', env('APP_FRONTEND_URL', 'http://localhost:3001')) }}" placeholder="https://myapp.com" required>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;">The URL of your frontend (Next.js/React).</p>
        </div>

        <div class="form-group">
            <label for="app_admin_url">Admin Panel URL</label>
            <input type="url" id="app_admin_url" name="app_admin_url" value="{{ old('app_admin_url', env('APP_ADMIN_URL', 'http://localhost:3000')) }}" placeholder="https://admin.myapp.com" required>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;">The URL of your admin panel (Next.js/React).</p>
        </div>

        <div class="form-group">
            <label for="purchase_code">Purchase Code (Optional)</label>
            <input type="text" id="purchase_code" name="purchase_code" value="{{ old('purchase_code') }}" placeholder="xxxx-xxxx-xxxx-xxxx">
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;">For CodeCanyon license verification (Optional for now).</p>
        </div>

        <div class="form-group">
            <label for="jwt_secret">JWT Secret (Optional)</label>
            <input type="text" id="jwt_secret" name="jwt_secret" value="{{ old('jwt_secret') }}" placeholder="Enter at least 32 characters or leave empty to auto-generate">
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;">Used for secure API authentication. Leaving this empty will automatically generate a secure 256-bit key.</p>
        </div>
    </form>
@endsection

@section('footer')
    <a href="{{ route('install.database') }}" class="btn btn-outline">Back</a>
    <button type="submit" form="env-form" class="btn btn-primary">
        Save & Continue
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 8px;"><path d="m9 18 6-6-6-6"/></svg>
    </button>
@endsection
