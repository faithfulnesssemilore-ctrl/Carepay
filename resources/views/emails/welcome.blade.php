<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Carepay</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f6f8fa;
            font-family: Arial, Helvetica, sans-serif;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border: 1px solid #d0d7de;
            border-radius: 12px;
            overflow: hidden;
        }

        .header {
            padding: 30px;
            text-align: center;
            background: linear-gradient(135deg, #a855f7 0%, #c084fc 100%);
            color: #ffffff;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            margin: 0;
        }

        .content {
            padding: 30px;
            color: #24292f;
        }

        .content h1 {
            font-size: 28px;
            margin: 0 0 20px 0;
            color: #a855f7;
        }

        .content p {
            line-height: 1.6;
            margin: 0 0 15px 0;
        }

        .highlight-box {
            background: #f6f8fa;
            border-left: 4px solid #a855f7;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
        }

        .features {
            margin: 20px 0;
        }

        .feature-item {
            padding: 10px 0;
            padding-left: 25px;
            position: relative;
        }

        .feature-item:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #a855f7;
            font-weight: bold;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #a855f7 0%, #c084fc 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 13px;
            color: #656d76;
            border-top: 1px solid #d8dee4;
            background: #f6f8fa;
        }

        .divider {
            height: 1px;
            background: #d8dee4;
            margin: 20px 0;
        }
    </style>
</head>

<body>

<div class="container">
    <div class="header">
        <p class="logo">Carepay</p>
        <p style="margin: 10px 0 0 0; font-size: 16px;">Welcome Aboard!</p>
    </div>

    <div class="content">
        <h1>Welcome to Carepay, {{ $user->first_name }}!</h1>

        <p>Hello {{ $user->first_name }} {{ $user->last_name }},</p>

        <p>We're excited to have you join Carepay, your trusted financial platform for seamless transactions and money management.</p>

        <div class="highlight-box">
            <strong>Account Details:</strong><br>
            Email: {{ $user->email }}<br>
            @if($user->phone)
            Phone: {{ $user->phone }}<br>
            @endif
            Account Created: {{ $user->created_at->format('M d, Y') }}
        </div>

        <p><strong>What you can now do:</strong></p>
        <div class="features">
            <div class="feature-item">Send money to anyone instantly</div>
            <div class="feature-item">Pay bills and buy airtime with ease</div>
            <div class="feature-item">Add funds to your wallet securely</div>
            <div class="feature-item">Track all your transactions</div>
            <div class="feature-item">Set your own security preferences</div>
        </div>

        <p>To get started, log in to your Carepay account and complete your profile setup. Don't forget to set a transaction PIN for added security!</p>

        <p>If you have any questions or need help, our support team is here for you 24/7.</p>

        <div class="divider"></div>

        <p>
            Regards,<br>
            <strong>The Carepay Team</strong>
        </p>
    </div>

    <div class="footer">
        <p>
            You're receiving this email because you just registered with Carepay.
        </p>
        <p>
            © {{ date('Y') }} Carepay. All rights reserved.
        </p>
    </div>
</div>

</body>
</html>
