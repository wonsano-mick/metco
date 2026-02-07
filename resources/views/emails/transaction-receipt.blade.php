<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Receipt - {{ $receiptData['transaction']['reference'] }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            padding: 30px;
            background: #f9fafb;
        }
        .transaction-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .amount {
            font-size: 32px;
            font-weight: bold;
            color: #059669;
            text-align: center;
            margin: 20px 0;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .detail-item {
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .button {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Transaction Receipt</h1>
        <p>Reference: {{ $receiptData['transaction']['reference'] }}</p>
    </div>
    
    <div class="content">
        <div class="transaction-card">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 14px; color: #6b7280;">Transaction Amount</div>
                <div class="amount">{{ $receiptData['transaction']['currency'] }} {{ $receiptData['transaction']['amount'] }}</div>
                <div style="background: #d1fae5; color: #065f46; padding: 8px 16px; border-radius: 20px; display: inline-block; font-weight: 600;">
                    {{ $receiptData['transaction']['status_display'] }}
                </div>
            </div>
            
            <div class="details-grid">
                <div class="detail-item">
                    <div style="font-size: 12px; color: #6b7280;">Type</div>
                    <div style="font-weight: 600;">{{ $receiptData['transaction']['type_display'] }}</div>
                </div>
                <div class="detail-item">
                    <div style="font-size: 12px; color: #6b7280;">Date</div>
                    <div style="font-weight: 600;">{{ $receiptData['transaction']['date'] }}</div>
                </div>
                <div class="detail-item">
                    <div style="font-size: 12px; color: #6b7280;">Time</div>
                    <div style="font-weight: 600;">{{ $receiptData['transaction']['time'] }}</div>
                </div>
                <div class="detail-item">
                    <div style="font-size: 12px; color: #6b7280;">Reference</div>
                    <div style="font-weight: 600;">{{ $receiptData['transaction']['reference'] }}</div>
                </div>
            </div>
            
            <div style="margin: 20px 0;">
                <div style="font-size: 12px; color: #6b7280; margin-bottom: 5px;">Description</div>
                <div>{{ $receiptData['transaction']['description'] }}</div>
            </div>
            
            @if($receiptData['parties']['source'])
            <div style="background: #fef2f2; padding: 15px; border-radius: 8px; margin: 15px 0;">
                <div style="font-size: 14px; font-weight: 600; color: #dc2626; margin-bottom: 10px;">
                    <i class="fas fa-arrow-up"></i> Debited Account
                </div>
                <div>{{ $receiptData['parties']['source']['account_number'] }}</div>
                <div style="font-size: 14px; color: #374151;">{{ $receiptData['parties']['source']['customer_name'] }}</div>
            </div>
            @endif
            
            @if($receiptData['parties']['destination'])
            <div style="background: #f0fdf4; padding: 15px; border-radius: 8px; margin: 15px 0;">
                <div style="font-size: 14px; font-weight: 600; color: #059669; margin-bottom: 10px;">
                    <i class="fas fa-arrow-down"></i> Credited Account
                </div>
                <div>{{ $receiptData['parties']['destination']['account_number'] }}</div>
                <div style="font-size: 14px; color: #374151;">{{ $receiptData['parties']['destination']['customer_name'] }}</div>
            </div>
            @endif
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('transactions.show', $receiptData['transaction']['id']) }}" class="button">
                View Full Details
            </a>
            <div style="font-size: 14px; color: #6b7280; margin-top: 10px;">
                A PDF receipt has been attached to this email
            </div>
        </div>
        
        <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; font-size: 13px; color: #6b7280;">
            <div style="font-weight: 600; margin-bottom: 10px;">Important Information:</div>
            <div>• This is an official transaction receipt</div>
            <div>• Keep this receipt for your records</div>
            <div>• Contact support if you have any questions</div>
        </div>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} {{ $receiptData['company']['name'] }}. All rights reserved.</p>
        <p>This email was sent automatically. Please do not reply to this message.</p>
        <p>{{ $receiptData['company']['address'] }}</p>
    </div>
</body>
</html>