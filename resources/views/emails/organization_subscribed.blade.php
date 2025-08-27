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
            margin-bottom: 15px;
        }
        p {
            color: #555;
            line-height: 1.6;
            font-size: 15px;
        }
        .panel {
            background: #f9fafb;
            border-left: 4px solid #4f46e5;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .panel strong {
            color: #2c3e50;
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
        <h1>📢 New Organization Subscription</h1>
        <p>A new organization has just subscribed to a plan.</p>

        <div class="panel">
            <p><strong>Organization Name:</strong> {{ $organization->name }}</p>
            <p><strong>Email:</strong> {{ $organization->email }}</p>
            <p><strong>Plan:</strong> {{ $plan->name }} (£{{ number_format($plan->price, 2) }}/{{ $plan->type }})</p>
            <p><strong>Subscribed On:</strong> {{ $organization->subscriptions->first()->created_at->format('d M Y, h:i A') }}</p>
        </div>

        <a href="{{ route('superadmin.organizations.show', $organization->id) }}" class="button">View Organization</a>

        <div class="footer">
            Regards,<br>
            {{ config('app.name') }} System
        </div>
    </div>
</body>
</html>
