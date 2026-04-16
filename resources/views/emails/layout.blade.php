<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{{ $site_settings['site_name'] }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7f6; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background: #000000; color: #ffffff; padding: 40px 20px; text-align: center; }
        .header img { max-width: 180px; height: auto; margin-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; }
        .content { padding: 40px; }
        .footer { background: #f9fafb; padding: 30px 20px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #edf2f7; }
        .footer p { margin: 5px 0; }
        .footer .brand { font-weight: 700; color: #374151; margin-bottom: 10px; }
        .button { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; margin-top: 20px; }
        @media only screen and (max-width: 600px) {
            .container { margin: 0; border-radius: 0; }
            .content { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(!empty($site_settings['site_logo']))
                <img src="{{ $site_settings['site_logo'] }}" alt="{{ $site_settings['site_name'] }}">
            @else
                <h1>{{ $site_settings['site_name'] }}</h1>
            @endif
        </div>
        
        <div class="content">
            @yield('content')
        </div>
        
        <div class="footer">
            <div class="brand">{{ $site_settings['site_name'] }}</div>
            @if(!empty($site_settings['site_address']))
                <p>{{ $site_settings['site_address'] }}</p>
            @endif
            @if(!empty($site_settings['site_phone']))
                <p>Phone: {{ $site_settings['site_phone'] }}</p>
            @endif
            <p>Email: <a href="mailto:{{ $site_settings['site_email'] }}" style="color: #2563eb; text-decoration: none;">{{ $site_settings['site_email'] }}</a></p>
            <p style="margin-top: 20px;">&copy; {{ date('Y') }} {{ $site_settings['site_name'] }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
