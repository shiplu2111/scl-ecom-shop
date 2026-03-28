<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribed</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background-color: #f4f7f6; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
        h1 { color: #2d3748; margin-top: 0; }
        p { color: #4a5568; line-height: 1.5; }
        .email { font-weight: bold; color: #3182ce; }
        .button { display: inline-block; margin-top: 1.5rem; padding: 0.5rem 1rem; background-color: #3182ce; color: white; text-decoration: none; border-radius: 4px; transition: background-color 0.2s; }
        .button:hover { background-color: #2b6cb0; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Unsubscribed</h1>
        <p>You have been successfully unsubscribed from our newsletter for <span class="email">{{ $email }}</span>.</p>
        <p>You will no longer receive promotional emails from us.</p>
        <a href="/" class="button">Return to Website</a>
    </div>
</body>
</html>
