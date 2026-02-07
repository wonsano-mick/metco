<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Receipt - {{ $receiptData['transaction']['reference'] ?? 'N/A' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #1f2937;
            line-height: 1.5;
        }
        
        .receipt-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px;
            background: white;
        }
        
        /* Header Styles */
        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: 700;
            color: #1d4ed8;
            margin-bottom: 8px;
        }
        
        .company-tagline {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        
        .receipt-title {
            text-align: right;
        }
        
        .receipt-title h1 {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }
        
        .receipt-number {
            font-size: 18px;
            color: #6b7280;
            font-weight: 500;
        }
        
        /* Status Banner */
        .status-banner {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .status-banner.failed {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .status-banner.pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .status-banner.reversed {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }
        
        .amount-display {
            font-size: 42px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .status-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Transaction Details */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .detail-section {
            background: #f9fafb;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e5e7eb;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: #3b82f6;
        }
        
        .detail-item {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
        }
        
        .detail-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }
        
        .detail-value {
            font-size: 15px;
            color: #111827;
            font-weight: 600;
            text-align: right;
        }
        
        .account-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .account-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }
        
        .account-type {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .account-number {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        }
        
        .customer-name {
            font-size: 16px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 5px;
        }
        
        .balance-info {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        
        .balance-label {
            font-size: 13px;
            color: #6b7280;
        }
        
        .balance-amount {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
        }
        
        /* Ledger Entries */
        .ledger-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 30px 0;
        }
        
        .ledger-table th {
            background: #f3f4f6;
            padding: 15px;
            text-align: left;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .ledger-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }
        
        .ledger-table tr:hover {
            background: #f9fafb;
        }
        
        .entry-type {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .entry-type.credit {
            background: #d1fae5;
            color: #065f46;
        }
        
        .entry-type.debit {
            background: #fee2e2;
            color: #991b1b;
        }
        
        /* Footer */
        .receipt-footer {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        
        .authorization {
            background: #f3f4f6;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
        }
        
        .signature-line {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
        }
        
        .signature-name {
            font-weight: 600;
            margin-top: 10px;
            color: #374151;
        }
        
        .signature-title {
            font-size: 13px;
            color: #6b7280;
        }
        
        /* QR Code */
        .qr-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
        }
        
        .qr-title {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 15px;
        }
        
        /* Print Styles */
        @media print {
            body {
                font-size: 12px;
            }
            
            .receipt-container {
                padding: 20px;
                max-width: none;
            }
            
            .no-print {
                display: none !important;
            }
            
            .status-banner {
                break-inside: avoid;
            }
            
            .details-grid {
                break-inside: avoid;
            }
            
            .receipt-footer {
                position: fixed;
                bottom: 0;
                width: 100%;
            }
        }
        
        /* Security Features */
        .security-features {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 20px;
            border-radius: 12px;
            margin: 30px 0;
            font-size: 13px;
            color: #6b7280;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(0, 0, 0, 0.03);
            font-weight: 700;
            pointer-events: none;
            z-index: -1;
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>
    <div class="watermark">{{ config('app.name', 'BANK') }}</div>
    
    <div class="receipt-container animate-fade-in">
        <!-- Header -->
        <div class="receipt-header">
            <div class="company-info">
                <div class="company-name">{{ 'METCO' }}</div>
                <div class="company-tagline">{{ 'P. O. Box 2, Goaso - Ahafo Region' }}</div>
                <div class="company-tagline">Phone: {{ '+233 554389606' }} | Email: {{ 'info.metco@gmail.com' }}</div>
                <div class="company-tagline">Website: {{ 'www.metco.com' }}</div>
            </div>
            <div class="receipt-title">
                <h1>OFFICIAL RECEIPT</h1>
                <div class="receipt-number">Reference: {{ $receiptData['transaction']['reference'] }}</div>
            </div>
        </div>
        
        <!-- Status Banner -->
        <div class="status-banner {{ $receiptData['transaction']['status'] }}">
            <div>
                <div style="font-size: 14px; margin-bottom: 5px; opacity: 0.9;">Transaction Amount</div>
                <div class="amount-display">{{ $receiptData['transaction']['currency'] }} {{ $receiptData['transaction']['amount'] }}</div>
            </div>
            <div class="status-badge">{{ $receiptData['transaction']['status_display'] }}</div>
        </div>
        
        <!-- Transaction Details Grid -->
        <div class="details-grid">
            <!-- Transaction Information -->
            <div class="detail-section">
                <div class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Transaction Information
                </div>
                <div class="detail-item">
                    <span class="detail-label">Reference Number:</span>
                    <span class="detail-value">{{ $receiptData['transaction']['reference'] }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Transaction Type:</span>
                    <span class="detail-value">{{ $receiptData['transaction']['type_display'] }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Date & Time:</span>
                    <span class="detail-value">{{ $receiptData['transaction']['date'] }} at {{ $receiptData['transaction']['time'] }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Description:</span>
                    <span class="detail-value">{{ $receiptData['transaction']['description'] }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Initiated By:</span>
                    <span class="detail-value">{{ $receiptData['transaction']['initiated_by'] }}</span>
                </div>
            </div>
            
            <!-- Amount Breakdown -->
            <div class="detail-section">
                <div class="section-title">
                    <i class="fas fa-calculator"></i>
                    Amount Breakdown
                </div>
                <div class="detail-item">
                    <span class="detail-label">Transaction Amount:</span>
                    <span class="detail-value">{{ $receiptData['transaction']['currency'] }} {{ $receiptData['transaction']['amount'] }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Amount in Words:</span>
                    <span class="detail-value">{{ App\Helpers\MoneyConverter::numberToWords($receiptData['transaction']['amount']) }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Fee Amount:</span>
                    <span class="detail-value">{{ $receiptData['transaction']['currency'] }} {{ $receiptData['transaction']['fee'] }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tax Amount:</span>
                    <span class="detail-value">{{ $receiptData['transaction']['currency'] }} {{ $receiptData['transaction']['tax'] }}</span>
                </div>
                <div class="detail-item" style="border-top: 2px solid #e5e7eb; padding-top: 15px; margin-top: 15px;">
                    <span class="detail-label" style="font-weight: 700;">Net Amount:</span>
                    <span class="detail-value" style="font-size: 18px; color: #1d4ed8;">{{ $receiptData['transaction']['currency'] }} {{ $receiptData['transaction']['net_amount'] }}</span>
                </div>
            </div>
        </div>
        
        <!-- Account Details -->
        <div class="details-grid">
            @if($receiptData['parties']['source'])
            <div class="detail-section">
                <div class="section-title">
                    <i class="fas fa-arrow-up text-red-500"></i>
                    Source Account
                </div>
                <div class="account-card">
                    <div class="account-type">Debited Account</div>
                    <div class="account-number">{{ $receiptData['parties']['source']['account_number'] }}</div>
                    <div class="customer-name">{{ $receiptData['parties']['source']['customer_name'] }}</div>
                    <div style="font-size: 13px; color: #6b7280; margin-top: 5px;">
                        Customer ID: {{ $receiptData['parties']['source']['customer_id'] }}
                    </div>
                    <div class="balance-info">
                        <div>
                            <div class="balance-label">Balance Before:</div>
                            <div class="balance-amount">{{ $receiptData['transaction']['currency'] }} {{ $receiptData['parties']['source']['balance_before'] }}</div>
                        </div>
                        <div>
                            <div class="balance-label">Balance After:</div>
                            <div class="balance-amount">{{ $receiptData['transaction']['currency'] }} {{ $receiptData['parties']['source']['balance_after'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            @if($receiptData['parties']['destination'])
            <div class="detail-section">
                <div class="section-title">
                    <i class="fas fa-arrow-down text-green-500"></i>
                    Destination Account
                </div>
                <div class="account-card">
                    <div class="account-type">Credited Account</div>
                    <div class="account-number">{{ $receiptData['parties']['destination']['account_number'] }}</div>
                    <div class="customer-name">{{ $receiptData['parties']['destination']['customer_name'] }}</div>
                    <div style="font-size: 13px; color: #6b7280; margin-top: 5px;">
                        Customer ID: {{ $receiptData['parties']['destination']['customer_id'] }}
                    </div>
                    <div class="balance-info">
                        <div>
                            <div class="balance-label">Balance Before:</div>
                            <div class="balance-amount">{{ $receiptData['transaction']['currency'] }} {{ $receiptData['parties']['destination']['balance_before'] }}</div>
                        </div>
                        <div>
                            <div class="balance-label">Balance After:</div>
                            <div class="balance-amount">{{ $receiptData['transaction']['currency'] }} {{ $receiptData['parties']['destination']['balance_after'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Ledger Entries -->
        @if(!empty($receiptData['ledger_entries']))
        <div class="detail-section">
            <div class="section-title">
                <i class="fas fa-book"></i>
                Ledger Entries
            </div>
            <table class="ledger-table">
                <thead>
                    <tr>
                        <th>Account Number</th>
                        <th>Entry Type</th>
                        <th>Amount</th>
                        <th>Balance Before</th>
                        <th>Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receiptData['ledger_entries'] as $entry)
                    <tr>
                        <td>{{ $entry['account_number'] }}</td>
                        <td>
                            <span class="entry-type {{ $entry['entry_type'] }}">
                                {{ $entry['entry_type_display'] }}
                            </span>
                        </td>
                        <td>{{ $entry['currency'] }} {{ $entry['amount'] }}</td>
                        <td>{{ $entry['currency'] }} {{ $entry['balance_before'] }}</td>
                        <td>{{ $entry['currency'] }} {{ $entry['balance_after'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        
        <!-- Security Features -->
        <div class="security-features">
            <div style="font-weight: 600; margin-bottom: 10px; color: #374151;">
                <i class="fas fa-shield-alt mr-2"></i>Security Features
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 15px; font-size: 12px;">
                <div>• Official Bank Document</div>
                <div>• Unique Reference: {{ $receiptData['transaction']['reference'] }}</div>
                <div>• Generated: {{ now()->format('Y-m-d H:i:s') }}</div>
                <div>• Document ID: {{ Str::random(16) }}</div>
            </div>
        </div>
        
        <!-- Authorization -->
        <div class="authorization">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 16px; font-weight: 600; color: #374151;">AUTHORIZED SIGNATURE</div>
                <div style="font-size: 13px; color: #6b7280; margin-top: 5px;">
                    This document serves as official proof of transaction
                </div>
            </div>
            
            <div class="signature-line">
                <div class="signature-box">
                    <div style="height: 60px; border-bottom: 2px solid #9ca3af; width: 200px;"></div>
                    <div class="signature-name">Authorized Signatory</div>
                    <div class="signature-title">Transaction Officer</div>
                </div>
                <div class="signature-box">
                    <div style="font-size: 24px; color: #1d4ed8;">
                        <i class="fas fa-stamp"></i>
                    </div>
                    <div class="signature-name">Digital Stamp</div>
                    <div class="signature-title">{{ $receiptData['company']['name'] }}</div>
                </div>
            </div>
        </div>
        
        <!-- QR Code Section -->
        <div class="qr-section">
            <div class="qr-title">Scan to Verify Transaction</div>
            <!-- QR Code would be generated here -->
            <div style="background: white; padding: 20px; display: inline-block; border-radius: 8px;">
                <div style="font-family: monospace; font-size: 12px; color: #6b7280;">
                    [QR Code: {{ $receiptData['transaction']['reference'] }}]
                </div>
            </div>
            <div style="font-size: 12px; color: #6b7280; margin-top: 10px;">
                Scan this code to verify transaction authenticity
            </div>
        </div>
        
        <!-- Footer -->
        <div class="receipt-footer">
            <div style="margin-bottom: 15px;">
                <strong>IMPORTANT:</strong> This is an official document. Please retain for your records.
            </div>
            <div>
                Generated electronically on {{ now()->format('F d, Y \a\t h:i A') }} • 
                Document ID: {{ Str::random(20) }} • 
                Page 1 of 1
            </div>
            <div style="margin-top: 10px; font-size: 12px; color: #9ca3af;">
                © {{ date('Y') }} {{ 'METCO' }}. All rights reserved.
                This document is computer-generated and requires no physical signature.
            </div>
        </div>
    </div>
    
    <!-- Print Button -->
    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px;">
        <button onclick="window.print()" style="
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        ">
            <i class="fas fa-print"></i>
            Print Receipt
        </button>
    </div>
    
    <script>
        // Auto-print if needed
        @if(request()->has('print'))
        window.onload = function() {
            window.print();
        }
        @endif
        
        // Add watermark security
        document.addEventListener('DOMContentLoaded', function() {
            // Prevent text selection
            document.body.style.userSelect = 'none';
            
            // Add copy protection
            document.addEventListener('copy', function(e) {
                e.clipboardData.setData('text/plain', 
                    'Official Document - ' + new Date().toLocaleString()
                );
                e.preventDefault();
            });
        });
    </script>
</body>
</html>