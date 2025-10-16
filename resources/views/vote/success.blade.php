<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote Submitted Successfully</title>
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
        .success-icon {
            font-size: 64px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .success-title {
            color: #333;
            margin: 0 0 15px 0;
            font-size: 24px;
        }
        .success-message {
            color: #666;
            margin: 0 0 30px 0;
            font-size: 16px;
            line-height: 1.5;
        }
        .checkmark {
            animation: checkmark 0.5s ease-in-out;
        }
        @keyframes checkmark {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .note {
            background: #e8f5e8;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon checkmark">✅</div>
        <h1 class="success-title">Vote Submitted Successfully!</h1>
        <p class="success-message">
            Thank you for participating in the voting. Your response has been recorded securely and anonymously.
        </p>

        <div class="note">
            <strong>Important:</strong> Your voting code has been used and cannot be used again.
            Results may be available after the voting period ends.
        </div>

        <p style="color: #666; font-size: 14px; margin-top: 30px;">
            You can now close this window or tab.
        </p>
    </div>

    <script>
        // Auto-refresh animation
        setTimeout(() => {
            const checkmark = document.querySelector('.checkmark');
            if (checkmark) {
                checkmark.style.animation = 'checkmark 0.5s ease-in-out';
            }
        }, 100);
    </script>
</body>
</html>