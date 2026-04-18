@extends('vendor.installer.layout')

@section('header')
    <h1>Server Requirements</h1>
    <p>We need to make sure your server is capable of running this application. Please ensure all requirements are met.</p>
@endsection

@section('content')
    <div class="requirements-list">
        @foreach($requirements as $key => $req)
            <div class="list-item">
                <span>{{ $req['name'] }}</span>
                @if($req['check'])
                    <span class="status-badge status-ok">Passed</span>
                @else
                    <span class="status-badge status-fail">Failed</span>
                @endif
            </div>
        @endforeach
    </div>

    @if(!$allPassed)
        <div class="alert alert-error" style="margin-top: 20px;">
            <p>Some requirements are not met. Please fix them before proceeding.</p>
        </div>
    @endif
@endsection

@section('footer')
    <a href="{{ route('install.index') }}" class="btn btn-outline">Back</a>
    <a href="{{ route('install.database') }}" class="btn btn-primary {{ !$allPassed ? 'disabled' : '' }}" {{ !$allPassed ? 'disabled' : '' }}>
        Continue
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 8px;"><path d="m9 18 6-6-6-6"/></svg>
    </a>
@endsection
