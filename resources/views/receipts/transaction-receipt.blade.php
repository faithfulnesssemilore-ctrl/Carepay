<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $transaction->reference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111; background: #fff; margin: 0; padding: 0; }
        .container { padding: 32px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .brand { font-size: 20px; font-weight: 700; color: #6b21a8; }
        .meta { text-align: right; }
        .meta div { font-size: 12px; color: #444; }
        .card { border: 1px solid #ececec; border-radius: 16px; padding: 24px; margin-bottom: 24px; }
        .label { font-size: 11px; color: #777; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 4px; }
        .value { font-size: 16px; font-weight: 600; color: #111; }
        .section { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
        .section .item { padding: 16px; background: #f8f5ff; border-radius: 12px; }
        .item .title { font-size: 12px; color: #555; margin-bottom: 8px; }
        .item .text { font-size: 14px; font-weight: 600; color: #111; }
        .footer { font-size: 12px; color: #555; line-height: 1.5; }
        .divider { height: 1px; background: #e8e3f7; margin: 24px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div class="brand">CarePay</div>
                <div style="font-size: 14px; color: #555;">Transaction Receipt</div>
            </div>
            <div class="meta">
                <div>{{ $transaction->created_at->format('F j, Y') }}</div>
                <div>{{ $transaction->created_at->format('h:i A') }}</div>
            </div>
        </div>

        <div class="card">
            <div class="section">
                <div class="item">
                    <div class="title">Reference</div>
                    <div class="text">{{ $transaction->reference }}</div>
                </div>
                <div class="item">
                    <div class="title">Status</div>
                    <div class="text">{{ ucfirst($transaction->status->value) }}</div>
                </div>
                <div class="item">
                    <div class="title">Type</div>
                    <div class="text">{{ ucfirst($transaction->type) }}</div>
                </div>
                <div class="item">
                    <div class="title">Amount</div>
                    <div class="text">₦{{ number_format($transaction->amount, 2) }}</div>
                </div>
            </div>

            <div class="divider"></div>

            <div>
                <div class="label">Description</div>
                <div class="value">{{ $transaction->description ?? 'No description available' }}</div>
            </div>

            <div class="divider"></div>

            <div class="section">
                <div class="item">
                    <div class="title">Customer</div>
                    <div class="text">{{ $transaction->user->fullName }}</div>
                </div>
                <div class="item">
                    <div class="title">Email</div>
                    <div class="text">{{ $transaction->user->email }}</div>
                </div>
            </div>
        </div>

        <div class="footer">
            This receipt confirms the successful completion of the transaction above. Keep this document for your records.
        </div>
    </div>
</body>
</html>
