@extends('vendor.installer.layout')

@section('header')
    <h1>Installation Successful!</h1>
    <p>Your application is now fully installed and ready to go. The installer has been disabled for security reasons.</p>
@endsection

@section('content')
    <div style="text-align: center; padding: 40px 0;">
        <div style="font-size: 5rem; margin-bottom: 20px;">🎉</div>
        <h2 style="margin-bottom: 10px;">You're all set!</h2>
        <p style="color: var(--text-muted); max-width: 500px; margin: 0 auto;">The .env file has been updated, migrations have been run, and your admin account has been created. You can now login to your dashboard.</p>
        
        <div style="margin-top: 30px; display: inline-block; padding: 20px; background: #f8fafc; border-radius: 12px; border: 1px dashed var(--border);">
            <p style="font-weight: 600; font-size: 0.9rem; margin-bottom: 10px;">Security Note:</p>
            <p style="font-size: 0.85rem; color: var(--text-muted);">The <code>storage/installed</code> file has been created. Access to the installer routes is now restricted. If you ever need to re-install, delete that file.</p>
        </div>
    </div>
@endsection

@section('footer')
    <a href="{{ config('app.frontend_url') }}" target="_blank" class="btn btn-outline" style="gap: 8px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        View Website
    </a>
    <a href="{{ config('app.admin_url') }}" target="_blank" class="btn btn-primary" style="gap: 8px;">
        Go to Admin Panel
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </a>
@endsection
