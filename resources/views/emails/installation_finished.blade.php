<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Installation Completed</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .content { padding: 20px 0; }
        .button-container { text-align: center; margin-top: 30px; }
        .button { display: inline-block; padding: 12px 24px; background-color: #6366f1; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 0 5px; }
        .button-secondary { background-color: #f1f5f9; color: #1e293b; border: 1px solid #e2e8f0; }
        .footer { margin-top: 30px; text-align: center; font-size: 0.8em; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>SCL-ECOM-SHOP Installation Completed</h2>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>Your application has been successfully installed and is now live. You can access your store and management panel using the links below:</p>
            
            <div class="button-container">
                <a href="{{ $frontendUrl }}" class="button button-secondary">Visit Website</a>
                <a href="{{ $adminUrl }}" class="button">Go to Admin Panel</a>
            </div>
            
            <p style="margin-top: 30px;"><strong>Live URLs:</strong></p>
            <ul>
                <li>Website: <a href="{{ $frontendUrl }}">{{ $frontendUrl }}</a></li>
                <li>Admin Panel: <a href="{{ $adminUrl }}">{{ $adminUrl }}</a></li>
            </ul>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} SCL-ECOM-SHOP. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
