@extends('vendor.installer.layout')

@section('header')
    <h1>Setting up Database</h1>
    <p>We are currently migrating your database and seeding initial data. This might take a few moments.</p>
@endsection

@section('content')
    <div id="migration-status" style="text-align: center; padding: 40px 0;">
        <div class="loader-container">
            <div class="spinner"></div>
        </div>
        <h3 id="status-text" style="margin-top: 20px;">Initializing Setup...</h3>
        <p id="sub-status-text" style="color: var(--text-muted); margin-top: 10px;">Please do not close this window.</p>
        
        <div id="error-box" style="display: none; margin-top: 20px;" class="alert alert-error">
            <p id="error-message"></p>
        </div>
    </div>

    <style>
        .loader-container {
            display: flex;
            justify-content: center;
        }
        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
@endsection

@section('footer')
    <div></div>
    <a href="{{ route('install.admin') }}" id="next-btn" class="btn btn-primary" style="display: none;">
        Continue to Admin Setup
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 8px;"><path d="m9 18 6-6-6-6"/></svg>
    </a>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusText = document.getElementById('status-text');
        const subStatusText = document.getElementById('sub-status-text');
        const nextBtn = document.getElementById('next-btn');
        const errorBox = document.getElementById('error-box');
        const errorMessage = document.getElementById('error-message');
        const spinner = document.querySelector('.spinner');

        setTimeout(() => {
            statusText.innerText = "Running Migrations...";
            
            fetch("{{ route('install.migration.run') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    statusText.innerText = "Setup Completed!";
                    subStatusText.innerText = "Database is ready for use.";
                    spinner.style.borderColor = "var(--success)";
                    spinner.style.borderTopColor = "var(--success)";
                    nextBtn.style.display = "inline-flex";
                } else {
                    throw new Error(data.message || "Something went wrong.");
                }
            })
            .catch(error => {
                statusText.innerText = "Setup Failed";
                subStatusText.innerText = "An error occurred during database setup.";
                errorBox.style.display = "block";
                errorMessage.innerText = error.message;
                spinner.style.display = "none";
            });
        }, 1500);
    });
</script>
@endsection
