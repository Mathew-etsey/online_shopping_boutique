<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Order Confirmation</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #EDE6D8;
            color: #0B0B0C;
        }
        table { border-collapse: collapse; }
        img { border: 0; display: block; }

        .badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            background: #f5f0e8;
            color: #0B0B0C;
        }
        .badge.pending { background: #fef3c7; color: #92400e; }
        .badge.payment_confirmed { background: #dbeafe; color: #1e40af; }
        .badge.processing { background: #e0e7ff; color: #3730a3; }
        .badge.ready_for_pickup { background: #d1fae5; color: #065f46; }
        .badge.completed { background: #d1fae5; color: #065f46; }
        .badge.cancelled { background: #fee2e2; color: #991b1b; }

        @media only screen and (max-width: 480px) {
            .container-table { width: 100% !important; }
            .body-cell { padding: 20px !important; }
            .header-cell { padding: 20px !important; }
        }
    </style>
</head>
<body>

    <!-- Outer wrapper table for background + centering (works in Outlook) -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#EDE6D8;">
        <tr>
            <td align="center" style="padding: 30px 12px;">

                <!-- Main container -->
                <table role="presentation" class="container-table" width="580" cellpadding="0" cellspacing="0" style="max-width:580px; width:100%; background:#ffffff; border:1px solid rgba(184,146,63,0.15);">

                    <!-- Header -->
                    <tr>
                        <td class="header-cell" align="center" style="background:#0B0B0C; padding:30px 30px 24px 30px; border-bottom:2px solid #B8923F;">
                            <img src="https://www.masterpiecegh.com/images/logos/logo-gold.png" alt="MASTERPIECE" width="120" style="max-height:40px; width:auto; margin:0 auto 10px auto;">
                            <div style="color:#EDE6D8; font-size:20px; font-weight:700; letter-spacing:0.2em; text-transform:uppercase;">
                                Order Confirmation
                            </div>
                            <div style="color:#B8923F; font-size:14px; letter-spacing:0.3em; margin-top:6px;">
                                MASTERPIECE
                            </div>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="body-cell" style="padding:30px 30px 20px;">

                            <p style="font-size:16px; font-weight:500; margin:0 0 4px 0;">
                                Hello <span style="color:#B8923F;">{{ $order->guest_name ?? $order->user->name ?? 'Valued Customer' }}</span>,
                            </p>
                            <p style="font-size:14px; color:#0B0B0C; opacity:0.6; margin:0 0 20px 0;">
                                Thank you for your order. We're excited to prepare your items!
                            </p>

                            <!-- Order Summary Box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#EDE6D8; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:16px 20px 4px 20px; font-size:14px; color:#0B0B0C; opacity:0.5;">Order Number</td>
                                    <td align="right" style="padding:16px 20px 4px 20px; font-size:14px; font-weight:500; color:#B8923F;">#{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 20px; font-size:14px; color:#0B0B0C; opacity:0.5;">Order Date</td>
                                    <td align="right" style="padding:4px 20px; font-size:14px; font-weight:500;">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 20px; font-size:14px; color:#0B0B0C; opacity:0.5;">Total Amount</td>
                                    <td align="right" style="padding:4px 20px; font-size:14px; font-weight:500; color:#B8923F;">GH₵ {{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 20px; font-size:14px; color:#0B0B0C; opacity:0.5;">Payment Method</td>
                                    <td align="right" style="padding:4px 20px; font-size:14px; font-weight:500;">Paystack</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding:8px 20px 0 20px;">
                                        <div style="border-top:1px solid rgba(0,0,0,0.06);"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 20px 16px 20px; font-size:14px; color:#0B0B0C; opacity:0.5;">Order Status</td>
                                    <td align="right" style="padding:8px 20px 16px 20px;">
                                        <span class="badge {{ $order->order_status }}">{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Items Table - UPDATED with Size and Color -->
                            <div style="margin:0 0 4px 0; font-size:13px; color:#0B0B0C; opacity:0.4; text-transform:uppercase; letter-spacing:0.05em;">Items</div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; margin-bottom:16px;">
                                <tr>
                                    <th align="left" style="padding:8px 0; font-size:12px; text-transform:uppercase; letter-spacing:0.05em; color:#0B0B0C; opacity:0.4; border-bottom:1px solid rgba(0,0,0,0.06); font-weight:normal;">Product</th>
                                    <th align="left" style="padding:8px 0; font-size:12px; text-transform:uppercase; letter-spacing:0.05em; color:#0B0B0C; opacity:0.4; border-bottom:1px solid rgba(0,0,0,0.06); font-weight:normal;">Qty</th>
                                    <th align="right" style="padding:8px 0; font-size:12px; text-transform:uppercase; letter-spacing:0.05em; color:#0B0B0C; opacity:0.4; border-bottom:1px solid rgba(0,0,0,0.06); font-weight:normal;">Price</th>
                                </tr>
                                @foreach($order->items as $item)
                                <tr>
                                    <td style="padding:10px 0; border-bottom:1px solid rgba(0,0,0,0.04);">
                                        {{ $item->product->name }}
                                        @if($item->size)
                                            <span style="font-size:11px; color:#B8923F; display:block; margin-top:2px;">
                                                Size: {{ $item->size }}
                                            </span>
                                        @endif
                                        @if($item->color)
                                            <span style="font-size:11px; color:#B8923F; display:block; margin-top:2px;">
                                                Color: {{ $item->color }}
                                            </span>
                                        @endif
                                    </td>
                                    <td style="padding:10px 0; border-bottom:1px solid rgba(0,0,0,0.04);">{{ $item->quantity }}</td>
                                    <td align="right" style="padding:10px 0; border-bottom:1px solid rgba(0,0,0,0.04);">GH₵ {{ number_format($item->price, 2) }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td colspan="2" align="right" style="font-weight:700; font-size:16px; padding-top:12px;">Total</td>
                                    <td align="right" style="font-weight:700; font-size:16px; padding-top:12px; color:#B8923F;">GH₵ {{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                            </table>

                            <!-- Delivery Information -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f0e8; margin:16px 0 20px;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <div style="margin:0 0 6px 0; font-size:13px; color:#0B0B0C; opacity:0.4; text-transform:uppercase; letter-spacing:0.05em;">Delivery Information</div>
                                        <p style="margin:0; font-size:14px;"><strong>Method:</strong> {{ ucfirst($order->delivery_method) }}</p>
                                        @if($order->delivery_address)
                                        <p style="margin:0; font-size:14px;"><strong>Address:</strong> {{ $order->delivery_address }}</p>
                                        @endif
                                        @if($order->delivery_zone)
                                        <p style="margin:0; font-size:14px;"><strong>Zone:</strong> {{ $order->delivery_zone }}</p>
                                        @endif
                                        <p style="margin:0; font-size:14px;">
                                            <strong>Estimated Date:</strong>
                                            @if($order->estimated_delivery_date)
                                                {{ $order->estimated_delivery_date->format('d M Y') }}
                                            @else
                                                To be confirmed
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            @if($order->order_notes)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f0e8; margin:0 0 16px;">
                                <tr>
                                    <td style="padding:12px 16px;">
                                        <p style="margin:0; font-size:13px; color:#0B0B0C; opacity:0.6;">
                                            <strong>Notes:</strong> {{ $order->order_notes }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background:#f8f6f0; padding:20px 30px; border-top:1px solid rgba(184,146,63,0.1);">
                            <p style="margin:4px 0; font-size:12px; color:#0B0B0C; opacity:0.6; font-weight:500;">Need help? Contact us</p>
                            <p style="margin:4px 0; font-size:12px; color:#0B0B0C; opacity:0.4;">
                                WhatsApp: <a href="https://wa.me/233204082142" style="color:#B8923F; text-decoration:none;">0204082142</a>
                            </p>
                            <p style="margin:8px 0; font-size:13px;">
                                <a href="https://www.instagram.com/masterpiecegh.official" style="color:#B8923F; text-decoration:none;">Instagram</a> ·
                                <a href="https://www.tiktok.com/@masterpiece.gh_" style="color:#B8923F; text-decoration:none;">TikTok</a> ·
                                <a href="https://www.snapchat.com/add/masterpiece.gh" style="color:#B8923F; text-decoration:none;">Snapchat</a>
                            </p>
                            <p style="margin:4px 0; font-size:12px; color:#0B0B0C; opacity:0.4;">&copy; {{ date('Y') }} MasterpieceGH. All rights reserved.</p>
                        </td>
                    </tr>

                </table>
                <!-- /Main container -->

            </td>
        </tr>
    </table>
    <!-- /Outer wrapper -->

</body>
</html>