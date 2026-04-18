@extends('vendor.installer.layout')

@section('header')
    <h1>Create Admin Account</h1>
    <p>Setup your primary administrator account. You'll use these credentials to access the backend panel.</p>
@endsection

@section('content')
    <form id="admin-form" action="{{ route('install.post.admin') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. John Doe" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="e.g. admin@example.com" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••" required>
        </div>
    </form>
@endsection

@section('footer')
    <div></div>
    <button type="submit" form="admin-form" class="btn btn-primary">
        Complete Installation
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 8px;"><path d="m9 18 6-6-6-6"/></svg>
    </button>
@endsection
