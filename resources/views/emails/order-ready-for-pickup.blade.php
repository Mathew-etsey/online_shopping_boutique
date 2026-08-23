<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Ready for Pickup</title>
    <style>
        /* Reset */
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

        /* Header */
        .header {
            background: #0B0B0C;
            padding: 30px 30px 24px 30px;
            text-align: center;
            border-bottom: 2px solid #B8923F;
        }
        .header h1 {
            margin: 0;
            color: #EDE6D8;
            font-size: 22px;
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
        .header .logo {
            margin-bottom: 8px;
        }
        .header .logo img {
            max-height: 40px;
            width: auto;
        }

        /* Body */
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
        .body .ready-message {
            font-size: 14px;
            color: #0B0B0C;
            opacity: 0.6;
            margin-bottom: 20px;
        }

        /* Success Badge */
        .success-badge {
            display: inline-block;
            background: #d1fae5;
            color: #065f46;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        /* Pickup Details Box */
        .pickup-box {
            background: #EDE6D8;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }
        .pickup-box h4 {
            margin: 0 0 8px 0;
            font-size: 13px;
            color: #0B0B0C;
            opacity: 0.4;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .pickup-box p {
            margin: 6px 0;
            font-size: 14px;
        }
        .pickup-box .highlight {
            color: #B8923F;
            font-weight: 500;
        }

        /* Items List - UPDATED with Size and Color */
        .items-box {
            background: #f5f0e8;
            border-radius: 12px;
            padding: 16px 20px;
            margin: 16px 0 20px;
        }
        .items-box h4 {
            margin: 0 0 8px 0;
            font-size: 13px;
            color: #0B0B0C;
            opacity: 0.4;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .items-box .item {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 14px;
        }
        .items-box .item .name {
            font-weight: 500;
        }
        .items-box .item .name .variation {
            font-size: 11px;
            color: #B8923F;
            font-weight: 400;
            display: block;
            margin-top: 2px;
        }
        .items-box .item .qty {
            opacity: 0.5;
        }
        .items-box .total {
            border-top: 1px solid rgba(0,0,0,0.06);
            margin-top: 8px;
            padding-top: 8px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
        }
        .items-box .total .gold {
            color: #B8923F;
        }

        /* Note Box */
        .note-box {
            background: #fef3c7;
            border-radius: 12px;
            padding: 12px 16px;
            margin: 16px 0 20px;
            border-left: 4px solid #B8923F;
        }
        .note-box p {
            margin: 0;
            font-size: 13px;
            color: #92400e;
        }

        /* Map Link */
        .map-link {
            display: inline-block;
            background: #B8923F;
            color: #0B0B0C;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            margin-top: 4px;
        }
        .map-link:hover {
            background: #D9B872;
        }

        /* Footer */
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
        .footer .social {
            margin-top: 8px;
            font-size: 13px;
        }
        .footer .social a {
            color: #B8923F;
            text-decoration: none;
            margin: 0 6px;
        }
        .footer .social a:hover {
            text-decoration: underline;
        }

        @media only screen and (max-width: 480px) {
            .container { margin: 10px; }
            .body { padding: 20px; }
            .header { padding: 20px; }
            .items-box .item { flex-wrap: wrap; }
        }
    </style>
</head>
<body>

    <div class="container">

        <!-- Header -->
        <div class="header">
            <div class="logo">
                <img src="https://www.masterpiecegh.com/images/logo-gold.png" alt="MASTERPIECE" style="max-height:40px; width:auto;">
            </div>
            <h1>🎉 Your Order is Ready!</h1>
            <div class="brand">MASTERPIECE</div>
        </div>

        <!-- Body -->
        <div class="body">

            <p class="greeting">Hello <span>{{ $order->guest_name ?? $order->user->name ?? 'Valued Customer' }}</span>,</p>
            <p class="ready-message">Your order is now ready for pickup at our boutique. We can't wait to see you!</p>

            <div style="text-align:center;">
                <span class="success-badge">✅ Ready for Pickup</span>
            </div>

            <h2>Order #{{ $order->order_number }}</h2>

            <!-- Pickup Details -->
            <div class="pickup-box">
                <h4>📍 Pickup Information</h4>
                <p><strong>Address:</strong> <span class="highlight">Accra, Ghana</span></p>
                <p><strong>Hours:</strong> Monday - Saturday, 9:00 AM - 6:00 PM</p>
                <p><strong>Phone:</strong> <span class="highlight">+233 24 412 3456</span></p>
                <p><strong>What to bring:</strong> Your ID and order confirmation</p>
                <div style="margin-top:10px;">
                    <a href="https://maps.google.com/maps?q=12+Osu+Road+Accra+Ghana" target="_blank" class="map-link">📍 Get Directions</a>
                </div>
            </div>

            <!-- Note -->
            <div class="note-box">
                <p><strong>⚠️ Please note:</strong> Orders not picked up within 7 days will be cancelled.</p>
            </div>

            <!-- Items - UPDATED with Size and Color -->
            <div class="items-box">
                <h4>Your Order</h4>
                @foreach($order->items as $item)
                <div class="item">
                    <span class="name">
                        {{ $item->product->name }}
                        @if($item->size)
                            <span class="variation">Size: {{ $item->size }}</span>
                        @endif
                        @if($item->color)
                            <span class="variation">Color: {{ $item->color }}</span>
                        @endif
                    </span>
                    <span class="qty">x{{ $item->quantity }}</span>
                </div>
                @endforeach
                <div class="total">
                    <span>Total</span>
                    <span class="gold">GH₵ {{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="font-weight:500; opacity:0.6;">Need help? Contact us</p>
            <p>📱 WhatsApp: <a href="https://wa.me/233244123456" style="color:#B8923F; text-decoration:none;">0244 123 456</a></p>
            <p class="social">
                <a href="https://www.instagram.com/masterpiecegh.official">Instagram</a> ·
                <a href="https://www.tiktok.com/@masterpiece.gh_">TikTok</a> ·
                <a href="https://www.snapchat.com/add/masterpiece.gh">Snapchat</a>
            </p>
            <p>&copy; {{ date('Y') }} MasterpieceGH. All rights reserved.</p>
        </div>

    </div>

</body>
</html>