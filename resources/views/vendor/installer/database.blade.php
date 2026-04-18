@extends('vendor.installer.layout')

@section('header')
    <h1>Database Configuration</h1>
    <p>Please enter your database connection details. These settings will be verified before we proceed.</p>
@endsection

@section('content')
    <form id="db-form" action="{{ route('install.post.database') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="db_host">Database Host</label>
            <input type="text" id="db_host" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" placeholder="127.0.0.1" required>
        </div>

        <div class="form-group">
            <label for="db_name">Database Name</label>
            <input type="text" id="db_name" name="db_name" value="{{ old('db_name') }}" placeholder="e.g. my_database" required>
        </div>

        <div class="form-group">
            <label for="db_user">Database Username</label>
            <input type="text" id="db_user" name="db_user" value="{{ old('db_user') }}" placeholder="e.g. root" required>
        </div>

        <div class="form-group">
            <label for="db_pass">Database Password</label>
            <input type="password" id="db_pass" name="db_pass" value="{{ old('db_pass') }}" placeholder="••••••••">
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;">Leave empty if no password is set.</p>
        </div>
    </form>
@endsection

@section('footer')
    <a href="{{ route('install.requirements') }}" class="btn btn-outline">Back</a>
    <button type="submit" form="db-form" class="btn btn-primary">
        Test & Continue
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 8px;"><path d="m9 18 6-6-6-6"/></svg>
    </button>
@endsection
