<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.05);
        }
        h1 {
            color: #2c3e50;
            font-size: 22px;
            margin-bottom: 10px;
        }
        p {
            color: #555;
            line-height: 1.6;
            font-size: 15px;
        }
        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background: #4f46e5;
            color: #fff !important;
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
        }
        .footer {
            margin-top: 25px;
            font-size: 13px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h1>🎉 Welcome to {{ config('app.name') }}, {{ $user->name }}!</h1>
        <p>
            Thank you for subscribing to the <strong>{{ $plan->name }}</strong> plan.  
            Your account is now active, and you can begin managing your clients, staff, and services right away.
        </p>

        <a href="{{ route('organization.dashboard') }}" class="button">Go to Your Dashboard</a>

        <div class="footer">
            Thanks,<br>
            The {{ config('app.name') }} Team
        </div>
    </div>
</body>
</html>
