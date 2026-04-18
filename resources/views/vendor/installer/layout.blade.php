<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Installer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --error: #ef4444;
            --radius: 12px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .installer-container {
            max-width: 900px;
            width: 100%;
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            display: grid;
            grid-template-columns: 300px 1fr;
            overflow: hidden;
            min-height: 600px;
        }

        @media (max-width: 768px) {
            .installer-container {
                grid-template-columns: 1fr;
            }
            .sidebar {
                display: none;
            }
        }

        .sidebar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar .brand {
            margin-bottom: 40px;
        }

        .sidebar .brand h2 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .steps {
            list-style: none;
        }

        .step-item {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            opacity: 0.6;
            transition: opacity 0.3s;
        }

        .step-item.active {
            opacity: 1;
        }

        .step-item.completed {
            opacity: 0.9;
        }

        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: 500;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .step-item.active .step-number {
            background: white;
            color: var(--primary);
            border-color: white;
        }

        .step-item.completed .step-number {
            background: var(--success);
            border-color: var(--success);
            color: white;
        }

        .step-label {
            font-weight: 500;
            font-size: 1rem;
        }

        .main-content {
            padding: 40px;
            display: flex;
            flex-direction: column;
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            color: var(--text-muted);
            line-height: 1.5;
        }

        .form-content {
            flex-grow: 1;
        }

        .footer-actions {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            background: var(--text-muted);
            cursor: not-allowed;
            transform: none;
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--text-main);
        }

        .btn-outline:hover {
            border-color: var(--text-muted);
            background: #f1f5f9;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-main);
        }

        input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-weight: 500;
        }

        .alert-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-ok {
            background: #dcfce7;
            color: #15803d;
        }

        .status-fail {
            background: #fee2e2;
            color: #b91c1c;
        }

        .list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .list-item:last-child {
            border-bottom: none;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade {
            animation: fadeIn 0.4s ease-out forwards;
        }
    </style>
</head>
<body>
    <div class="installer-container animate-fade">
        <aside class="sidebar">
            <div>
                <div class="brand">
                    <h2>{{ config('app.name', 'Laravel') }}</h2>
                    <p style="font-size: 0.85rem; opacity: 0.8">System Installer v1.0</p>
                </div>
                
                <ul class="steps">
                    @php
                        $currentRoute = Route::currentRouteName();
                        $steps = [
                            ['name' => 'install.index', 'label' => 'Welcome'],
                            ['name' => 'install.requirements', 'label' => 'Server Check'],
                            ['name' => 'install.database', 'label' => 'Database'],
                            ['name' => 'install.environment', 'label' => 'Environment'],
                            ['name' => 'install.migration', 'label' => 'Setup'],
                            ['name' => 'install.admin', 'label' => 'Admin Account'],
                            ['name' => 'install.finish', 'label' => 'Finish'],
                        ];
                        $foundCurrent = false;
                    @endphp

                    @foreach($steps as $index => $step)
                        @php
                            $isCurrent = $currentRoute == $step['name'];
                            if ($isCurrent) $foundCurrent = true;
                            $isCompleted = !$foundCurrent && $currentRoute != $step['name'];
                        @endphp
                        <li class="step-item {{ $isCurrent ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }}">
                            <div class="step-number">
                                @if($isCompleted)
                                    ✓
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </div>
                            <span class="step-label">{{ $step['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="sidebar-footer">
                <p style="font-size: 0.8rem; opacity: 0.7">&copy; {{ date('Y') }} All Rights Reserved.</p>
            </div>
        </aside>

        <main class="main-content">
            <div class="header">
                @yield('header')
            </div>

            @if($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-content">
                @yield('content')
            </div>

            <div class="footer-actions">
                @yield('footer')
            </div>
        </main>
    </div>

    @yield('scripts')
</body>
</html>
