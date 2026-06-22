<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Receipt</title>
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

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            font-size: 12px;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .content {
            padding: 30px;
            color: #24292f;
        }

        .content h1 {
            font-size: 24px;
            margin: 0 0 10px 0;
            color: #a855f7;
        }

        .amount-box {
            text-align: center;
            background: #f6f8fa;
            border: 2px solid #a855f7;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        .amount {
            font-size: 36px;
            font-weight: bold;
            color: #a855f7;
            margin: 0;
        }

        .amount-description {
            color: #656d76;
            font-size: 14px;
            margin: 5px 0 0 0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #656d76;
            font-size: 14px;
        }

        .detail-value {
            color: #24292f;
            font-weight: 500;
            text-align: right;
        }

        .details-section {
            margin: 20px 0;
            padding: 15px;
            background: #f6f8fa;
            border-radius: 8px;
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

        .success-badge {
            display: inline-block;
            width: 24px;
            height: 24px;
            background: #22c55e;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            font-weight: bold;
            margin-right: 8px;
        }

        .completed {
            color: #22c55e;
            font-weight: 500;
        }
    </style>
</head>

<body>

<div class="container">
    <div class="header">
        <p class="logo">Carepay</p>
        <p style="margin: 10px 0 0 0; font-size: 16px;">Transaction Receipt</p>
    </div>

    <div class="content">
        <h1>
            <span class="success-badge">✓</span>
            <span class="completed">Transaction Completed</span>
        </h1>

        <p>Hi {{ $transaction->user->first_name }},</p>

        <p>Your transaction has been successfully processed. Here are the details:</p>

        <div class="amount-box">
            <p class="amount">₦{{ number_format($transaction->amount, 2) }}</p>
            <p class="amount-description">
                @if($transaction->type === 'debit')
                    Money Sent
                @elseif($transaction->type === 'credit')
                    Money Received
                @else
                    Transaction Amount
                @endif
            </p>
        </div>

        <div class="details-section">
            <div class="detail-row">
                <span class="detail-label">Reference ID:</span>
                <span class="detail-value">{{ $transaction->reference }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Transaction Type:</span>
                <span class="detail-value">
                    @if($transaction->category === 'transfer')
                        Bank Transfer
                    @elseif($transaction->category === 'bill')
                        Bill Payment
                    @elseif($transaction->category === 'deposit')
                        Deposit
                    @else
                        {{ ucfirst($transaction->category) }}
                    @endif
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Description:</span>
                <span class="detail-value">{{ $transaction->description }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Date & Time:</span>
                <span class="detail-value">{{ $transaction->created_at->format('M d, Y H:i') }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value completed">✓ Completed</span>
            </div>
        </div>

        <p>Your wallet balance is now: <strong>₦{{ number_format($transaction->user->wallet->balance, 2) }}</strong></p>

        <p>You can download your full receipt or view all your transactions by logging into your Carepay account.</p>

        <div class="divider"></div>

        <p>
            Regards,<br>
            <strong>The Carepay Team</strong>
        </p>

        <p style="font-size: 12px; color: #656d76;">
            This email confirms your transaction. For security, never share your transaction details with anyone.
        </p>
    </div>

    <div class="footer">
        <p>
            You're receiving this email because a transaction was completed on your Carepay account.
        </p>
        <p>
            © {{ date('Y') }} Carepay. All rights reserved.
        </p>
    </div>
</div>

</body>
</html>
