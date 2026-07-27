<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Update</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Inter', Arial, sans-serif;
            background-color: #EDE6D8;
            color: #0B0B0C;
        }
        
        .container {
            max-width: 580px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(184, 146, 63, 0.15);
            box-shadow: 0 8px 30px rgba(11, 11, 12, 0.08);
        }

        .header {
            background: #0B0B0C;
            padding: 30px 30px 24px 30px;
            text-align: center;
            border-bottom: 2px solid #B8923F;
        }
        .header h1 {
            margin: 0;
            color: #EDE6D8;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
        .header .brand {
            color: #B8923F;
            font-size: 12px;
            letter-spacing: 0.3em;
            margin-top: 4px;
            text-transform: uppercase;
        }

        .body {
            padding: 30px 30px 20px;
        }
        .body h2 {
            font-size: 18px;
            margin-top: 0;
            margin-bottom: 4px;
            color: #0B0B0C;
        }
        .body .greeting {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 4px;
        }
        .body .greeting span {
            color: #B8923F;
        }

        .status-box {
            background: #EDE6D8;
            border-radius: 12px;
            padding: 16px 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .status-box .status {
            font-size: 20px;
            font-weight: 700;
            color: #B8923F;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-box .status.pending { color: #92400e; }
        .status-box .status.payment_confirmed { color: #1e40af; }
        .status-box .status.processing { color: #3730a3; }
        .status-box .status.ready_for_pickup { color: #065f46; }
        .status-box .status.completed { color: #065f46; }
        .status-box .status.cancelled { color: #991b1b; }

        .order-box {
            background: #f5f0e8;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }
        .order-box .row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 14px;
        }
        .order-box .row .label {
            color: #0B0B0C;
            opacity: 0.5;
        }
        .order-box .row .value {
            font-weight: 500;
        }
        .order-box .row .value.gold {
            color: #B8923F;
        }

        .next-box {
            background: #d1fae5;
            border-radius: 12px;
            padding: 16px 20px;
            margin: 16px 0 20px;
            border-left: 4px solid #B8923F;
        }
        .next-box p {
            margin: 4px 0;
            font-size: 14px;
            color: #065f46;
        }

        .footer {
            background: #f8f6f0;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid rgba(184, 146, 63, 0.1);
        }
        .footer p {
            margin: 4px 0;
            font-size: 12px;
            color: #0B0B0C;
            opacity: 0.4;
        }
        .footer .social a {
            color: #B8923F;
            text-decoration: none;
            margin: 0 6px;
        }

        @media only screen and (max-width: 480px) {
            .container { margin: 10px; }
            .body { padding: 20px; }
            .header { padding: 20px; }
        }
    </style>
</head>
<body>

    <div class="container">

        <div class="header">
            <h1>Order Status Update</h1>
            <div class="brand">MASTERPIECE </div>
        </div>

        <div class="body">

            @php
                $customerName = $order->user->name ?? $order->guest_name ?? 'Valued Customer';
            @endphp

            <p class="greeting">Hello <span>{{ $customerName }}</span>,</p>

            <div class="status-box">
                <div class="status {{ $order->order_status }}">
                    {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                </div>
                <p style="margin: 4px 0 0; font-size:13px; color:#0B0B0C; opacity:0.5;">
                    Your order status has been updated
                </p>
            </div>

            <h2>Order #{{ $order->order_number }}</h2>

            <div class="order-box">
                <div class="row">
                    <span class="label">Order Number</span>
                    <span class="value gold">#{{ $order->order_number }}</span>
                </div>
                <div class="row">
                    <span class="label">Total Amount</span>
                    <span class="value gold">GH₵ {{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="row">
                    <span class="label">Status</span>
                    <span class="value">{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</span>
                </div>
            </div>

            <div class="next-box">
                <p><strong>Next Steps:</strong></p>
                @if($order->order_status == 'ready_for_pickup')
                    <p>Your order is ready for pickup . Please bring your order ID and order confirmation.</p>
                @elseif($order->order_status == 'completed')
                    <p>Your order has been completed. Thank you for shopping with MASTERPIECE!</p>
                @elseif($order->order_status == 'cancelled')
                    <p>Your order has been cancelled. If you have any questions, please contact us.</p>
                @else
                    <p>We will notify you when your order status changes.</p>
                @endif
            </div>

        </div>

        <div class="footer">
            <p style="font-weight:500; opacity:0.6;">Need help? Contact us</p>
            <p>📱 WhatsApp: <a href="https://wa.me/233244123456" style="color:#B8923F; text-decoration:none;">0244 123 456</a></p>
            <p class="social">
                <a href="https://www.instagram.com/masterpiecegh.official">Instagram</a> ·
                <a href="https://www.tiktok.com/@masterpiece.gh_">TikTok</a>
            </p>
            <p>&copy; {{ date('Y') }} MasterpieceGH. All rights reserved.</p>
        </div>

    </div>

</body>
</html>