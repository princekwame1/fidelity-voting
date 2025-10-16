<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Error</title>
    @vite(['resources/js/app.js'])
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 500px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
        }
        .error-icon {
            font-size: 64px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .error-title {
            color: #333;
            margin: 0 0 15px 0;
            font-size: 24px;
        }
        .error-message {
            color: #666;
            margin: 0 0 30px 0;
            font-size: 16px;
            line-height: 1.5;
        }
        .error-code {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 14px;
            color: #dc3545;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #0056b3;
            text-decoration: none;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">Unable to Access Voting</h1>
        <p class="error-message">{{ $error }}</p>

        <div class="error-code">
            This could happen if:
            <ul style="text-align: left; margin: 10px 0; padding-left: 20px;">
                <li>You have already voted from this device</li>
                <li>The voting period has ended</li>
                <li>Your voting session has expired</li>
                <li>The QR code is invalid or corrupted</li>
            </ul>
        </div>

        <p style="color: #666; font-size: 14px; margin: 20px 0;">
            If you believe this is an error, please contact the event organizer.
        </p>
    </div>
</body>
</html>