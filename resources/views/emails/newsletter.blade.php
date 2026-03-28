<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f6f9fc; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .wrapper { width: 100%; background-color: #f6f9fc; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { background-color: #2563eb; padding: 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.025em; }
        .content { padding: 40px; color: #374151; line-height: 1.6; font-size: 16px; }
        .content h1, .content h2, .content h3 { color: #111827; margin-top: 0; }
        .content img { max-width: 100%; height: auto; border-radius: 8px; }
        .content a { color: #2563eb; text-decoration: underline; }
        .footer { padding: 30px; background-color: #f9fafb; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer p { margin: 0; color: #6b7280; font-size: 14px; }
        .footer-links { margin-top: 15px; }
        .unsubscribe { color: #9ca3af; text-decoration: none; font-size: 12px; }
        .unsubscribe:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>{{ config('app.name') }}</h1>
            </div>
            <div class="content">
                {!! $content !!}
            </div>
            <div class="footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                @if(isset($unsubscribe_url))
                <div class="footer-links">
                    <a href="{{ $unsubscribe_url }}" class="unsubscribe">Unsubscribe from these emails</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
