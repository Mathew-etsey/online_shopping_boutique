<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Request</title>
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
        .header .logo {
            margin-bottom: 8px;
        }
        .header .logo img {
            max-height: 40px;
            width: auto;
        }
        .header .emoji {
            font-size: 28px;
            display: block;
            margin-bottom: 4px;
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
        .body .message {
            font-size: 14px;
            color: #0B0B0C;
            opacity: 0.6;
            margin-bottom: 20px;
        }

        /* Order Items Box */
        .items-box {
            background: #EDE6D8;
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
            padding: 10px 0;
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        .items-box .item:last-child {
            border-bottom: none;
        }
        .items-box .item .name {
            font-weight: 500;
            font-size: 14px;
        }
        .items-box .item .details {
            font-size: 13px;
            color: #0B0B0C;
            opacity: 0.5;
            margin-top: 2px;
        }
        .items-box .item .stars {
            font-size: 22px;
            letter-spacing: 2px;
            margin-top: 4px;
            color: #B8923F;
        }
        .items-box .item .stars .empty {
            color: #ddd;
        }

        /* Review Button */
        .btn-review {
            display: inline-block;
            background: #B8923F;
            color: #0B0B0C;
            padding: 12px 36px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin-top: 4px;
            transition: background 0.3s;
        }
        .btn-review:hover {
            background: #D9B872;
        }

        /* Why Review */
        .why-box {
            background: #f5f0e8;
            border-radius: 12px;
            padding: 16px 20px;
            margin: 16px 0 20px;
        }
        .why-box p {
            margin: 4px 0;
            font-size: 13px;
            color: #0B0B0C;
            opacity: 0.6;
        }
        .why-box .check {
            color: #B8923F;
            font-weight: 500;
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
            .items-box .item .stars { font-size: 18px; }
        }
    </style>
</head>
<body>

    <div class="container">

        <!-- Header -->
        <div class="header">
            <div class="logo">
                <img src="https://yourdomain.com/images/logo-gold.png" alt="MASTERPIECE" style="max-height:40px; width:auto;">
            </div>
            <span class="emoji">📝</span>
            <h1>We Value Your Feedback!</h1>
            <div class="brand">MASTERPIECE</div>
        </div>

        <!-- Body -->
        <div class="body">

            <p class="greeting">Hello <span>{{ $order->user->name ?? $order->guest_name ?? 'Valued Customer' }}</span>,</p>
            <p class="message">
                Thank you for your recent purchase from MASTERPIECE. We hope you're enjoying your new items! 
                Your feedback helps us improve and helps other customers make informed decisions.
            </p>

            <!-- Order Items -->
            <div class="items-box">
                <h4>Order #{{ $order->order_number }}</h4>
                @foreach($order->items as $item)
                <div class="item">
                    <div class="name">{{ $item->product->name }}</div>
                    <div class="details">Quantity: {{ $item->quantity }} | Price: GH₵ {{ number_format($item->price, 2) }}</div>
                    <div class="stars">
                        ★★★★★
                        <span style="font-size:12px; color:#0B0B0C; opacity:0.3; font-weight:400; margin-left:6px;">
                            Click to rate
                        </span>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Why Review -->
            <div class="why-box">
                <p><span class="check">✅</span> Your reviews help us serve you better</p>
                <p><span class="check">✅</span> Help other customers make the right choice</p>
                <p><span class="check">✅</span> We read every single review</p>
            </div>

            <!-- Review Button -->
            <div style="text-align: center; margin: 20px 0 10px;">
                <a href="#" class="btn-review">⭐ Leave a Review</a>
            </div>

            <p style="text-align: center; font-size: 13px; color: #0B0B0C; opacity: 0.4; margin-top: 10px;">
                Your honest feedback helps us serve you better.
            </p>

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