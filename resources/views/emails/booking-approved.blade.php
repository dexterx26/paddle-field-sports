<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Court Reservation Approved – {{ $settings->venue_name ?? 'Paddle Field Sports Center' }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #faf6f0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #2d3748; -webkit-font-smoothing: antialiased; line-height: 1.6;">

    <!-- Wrapper Table -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #faf6f0; padding: 30px 10px;">
        <tr>
            <td align="center">
                <!-- Main Container Card (Max 600px) -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 25px rgba(30, 35, 42, 0.08); border: 1px solid #e2dbcf;">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e232a 0%, #12151b 100%); padding: 36px 30px; text-align: center; border-bottom: 3px solid #0891b2;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <!-- Brand Logo -->
                                        @php
                                            $logoPath = $settings->getEmailLogoPath();
                                            $logoSrc = null;
                                            if ($logoPath && isset($message)) {
                                                $logoSrc = $message->embed($logoPath);
                                            } elseif (!empty($settings->logo_url) && !str_contains($settings->logo_url, 'localhost') && !str_contains($settings->logo_url, '127.0.0.1')) {
                                                $logoSrc = $settings->logo_url;
                                            } elseif (file_exists(public_path('images/default-logo.png'))) {
                                                $logoSrc = asset('images/default-logo.png');
                                            }
                                        @endphp

                                        @if($logoSrc)
                                            <div style="display: inline-block; width: 68px; height: 68px; border-radius: 18px; background-color: #ffffff; padding: 6px; margin-bottom: 12px; box-shadow: 0 4px 14px rgba(8, 145, 178, 0.4); box-sizing: border-box;">
                                                <img src="{{ $logoSrc }}" alt="{{ $settings->venue_name ?? 'Paddle Field Sports Center' }}" width="56" height="56" style="display: block; width: 100%; height: 100%; object-fit: contain; border-radius: 12px;">
                                            </div>
                                        @else
                                            <div style="display: inline-block; width: 56px; height: 56px; line-height: 56px; border-radius: 16px; background: linear-gradient(135deg, #b88250 0%, #0891b2 100%); color: #ffffff; font-size: 26px; font-weight: bold; text-align: center; margin-bottom: 12px; box-shadow: 0 4px 12px rgba(8, 145, 178, 0.35);">
                                                🏓
                                            </div>
                                        @endif
                                        <h1 style="margin: 0; color: #f7fafc; font-size: 22px; font-weight: 800; letter-spacing: -0.5px; text-transform: uppercase;">
                                            {{ $settings->venue_name ?? 'Paddle Field Sports Center' }}
                                        </h1>
                                        <p style="margin: 4px 0 0 0; color: #d4a373; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px;">
                                            {{ $settings->tagline ?? 'Premier Indoor Pickleball Facility' }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 36px 32px;">
                            
                            <!-- Status Pill Badge -->
                            @if($booking->isPaidWithCredits())
                                <div style="background-color: #ecfdf5; border-left: 4px solid #10b981; padding: 12px 18px; border-radius: 8px; margin-bottom: 24px;">
                                    <p style="margin: 0; color: #065f46; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                                        ⚡ Instant Approval & Confirmed (Court Credits)
                                    </p>
                                    <p style="margin: 4px 0 0 0; color: #047857; font-size: 13px;">
                                        Your booking was paid using your prepaid Court Credits and is immediately locked in.
                                    </p>
                                </div>
                            @elseif($booking->approved_by)
                                <div style="background-color: #ecfeff; border-left: 4px solid #0891b2; padding: 12px 18px; border-radius: 8px; margin-bottom: 24px;">
                                    <p style="margin: 0; color: #0e7490; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                                        ✓ Approved by Court Management
                                    </p>
                                    <p style="margin: 4px 0 0 0; color: #155e75; font-size: 13px;">
                                        Your payment receipt has been reviewed and your reservation is officially confirmed.
                                    </p>
                                </div>
                            @else
                                <div style="background-color: #ecfeff; border-left: 4px solid #0891b2; padding: 12px 18px; border-radius: 8px; margin-bottom: 24px;">
                                    <p style="margin: 0; color: #0e7490; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                                        ✓ Reservation Confirmed
                                    </p>
                                    <p style="margin: 4px 0 0 0; color: #155e75; font-size: 13px;">
                                        Your court reservation has been confirmed and is ready for your match.
                                    </p>
                                </div>
                            @endif

                            <!-- Greeting -->
                            <h2 style="margin: 0 0 12px 0; color: #1e232a; font-size: 22px; font-weight: 800;">
                                Hello, {{ $booking->customer_name }}! 🏓
                            </h2>
                            <p style="margin: 0 0 20px 0; font-size: 14px; color: #596579; line-height: 1.6;">
                                Great news! Your court reservation at <strong>{{ $settings->venue_name ?? 'Paddle Field Sports Center' }}</strong> has been confirmed. Below are your official booking details and digital pass information:
                            </p>

                            <!-- Reservation Details Card -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #faf6f0; border-radius: 14px; border: 1px solid #eae3d7; margin: 24px 0; overflow: hidden;">
                                <tr>
                                    <td colspan="2" style="background-color: #efe7dc; padding: 12px 18px; border-bottom: 1px solid #e2dbcf;">
                                        <strong style="color: #1e232a; font-size: 12px; text-transform: uppercase; letter-spacing: 0.8px;">
                                            📋 Official Reservation Summary
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #f0e9df; font-size: 13px; color: #718096; width: 38%;">
                                        Booking Reference:
                                    </td>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #f0e9df; font-size: 13px; color: #0891b2; font-weight: 800; font-family: monospace;">
                                        {{ $booking->booking_reference }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #f0e9df; font-size: 13px; color: #718096;">
                                        Reserved Court:
                                    </td>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #f0e9df; font-size: 13px; color: #1e232a; font-weight: 700;">
                                        {{ $booking->court->name ?? 'Pickleball Court' }}
                                        @if(!empty($booking->court->surface_type))
                                            <span style="font-size: 11px; color: #64748b; font-weight: normal;">({{ $booking->court->surface_type }})</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #f0e9df; font-size: 13px; color: #718096;">
                                        Reservation Date:
                                    </td>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #f0e9df; font-size: 13px; color: #1e232a; font-weight: 700;">
                                        {{ $booking->booking_date->format('l, F j, Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #f0e9df; font-size: 13px; color: #718096;">
                                        Time Slot(s):
                                    </td>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #f0e9df; font-size: 13px; color: #1e232a;">
                                        <strong style="color: #0891b2;">
                                            {{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }} – 
                                            {{ \Carbon\Carbon::parse($booking->end_time)->format('g:i A') }}
                                        </strong>
                                        <span style="font-size: 11px; color: #64748b; font-weight: bold;">({{ $booking->total_hours }} hr{{ $booking->total_hours > 1 ? 's' : '' }})</span>
                                        
                                        @if($booking->slots && $booking->slots->count() > 1)
                                            <div style="margin-top: 4px; font-size: 11px; color: #64748b;">
                                                @foreach($booking->slots->sortBy('slot_time') as $s)
                                                    @php
                                                        $slotCarbon = \Carbon\Carbon::parse($s->slot_time);
                                                        $stLabel = $slotCarbon->format('ga');
                                                        $endLabel = $slotCarbon->copy()->addHour()->format('ga');
                                                    @endphp
                                                    <span style="display: inline-block; background: #e2dbcf; padding: 2px 6px; border-radius: 4px; margin-right: 4px; margin-bottom: 2px;">
                                                        {{ $stLabel }} - {{ $endLabel }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #f0e9df; font-size: 13px; color: #718096;">
                                        Players / Group:
                                    </td>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #f0e9df; font-size: 13px; color: #1e232a; font-weight: 600;">
                                        {{ $booking->players_count ?? 4 }} Players
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #f0e9df; font-size: 13px; color: #718096;">
                                        Payment Method:
                                    </td>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #f0e9df; font-size: 13px; color: #1e232a; font-weight: 600;">
                                        @if($booking->isPaidWithCredits())
                                            <span style="color: #059669; font-weight: 700;">Court Credits Wallet ({{ number_format($booking->credits_used, 1) }} Credits)</span>
                                        @elseif($booking->payment_method === 'manual_receipt')
                                            <span>Manual Transfer (GCash / Bank) – Verified</span>
                                        @elseif($booking->payment_method === 'paymongo')
                                            <span>PayMongo Online Payment (Paid)</span>
                                        @elseif($booking->payment_method === 'xendit')
                                            <span>Xendit Online Gateway (Paid)</span>
                                        @else
                                            <span style="text-transform: capitalize;">{{ str_replace('_', ' ', $booking->payment_method) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #f0e9df; font-size: 13px; color: #718096;">
                                        Total Amount:
                                    </td>
                                    <td style="padding: 14px 18px; border-bottom: 1px solid #f0e9df; font-size: 14px; color: #047857; font-weight: 800;">
                                        @if($booking->isPaidWithCredits())
                                            {{ number_format($booking->credits_used, 1) }} Credits ({{ $booking->formatted_amount }} value)
                                        @else
                                            {{ $booking->formatted_amount }}
                                        @endif
                                    </td>
                                </tr>
                                @if(!empty($booking->approver))
                                    <tr>
                                        <td style="padding: 14px 18px; font-size: 13px; color: #718096;">
                                            Approved By:
                                        </td>
                                        <td style="padding: 14px 18px; font-size: 13px; color: #1e232a; font-weight: 600;">
                                            {{ $booking->approver->name }} (Court Management)
                                        </td>
                                    </tr>
                                @endif
                            </table>

                            <!-- Prominent Digital Pass CTA Button -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $trackUrl }}" target="_blank" style="display: inline-block; padding: 16px 36px; font-size: 14px; font-weight: 800; color: #12151b; background: linear-gradient(135deg, #0891b2 0%, #22d3ee 100%); text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px; border-radius: 12px; box-shadow: 0 4px 14px rgba(8, 145, 178, 0.4);">
                                            🎟️ View Digital Pass & Tracker
                                        </a>
                                        <p style="margin: 10px 0 0 0; font-size: 11px; color: #888888;">
                                            Present this digital pass or booking reference when you arrive at the clubhouse.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Facility Rules & Reminders -->
                            <div style="background-color: #faf6f0; border-radius: 12px; padding: 18px 20px; border: 1px solid #eae3d7; margin-top: 24px;">
                                <h4 style="margin: 0 0 10px 0; color: #1e232a; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                                    📌 Clubhouse Reminders for Your Match:
                                </h4>
                                <ul style="margin: 0; padding-left: 20px; font-size: 12px; color: #596579; line-height: 1.6;">
                                    <li style="margin-bottom: 6px;"><strong>Footwear:</strong> Non-marking indoor court shoes are mandatory to protect our certified acrylic surfaces.</li>
                                    <li style="margin-bottom: 6px;"><strong>Arrival:</strong> Please arrive 10–15 minutes before your scheduled timeslot to warm up and check in.</li>
                                    <li style="margin-bottom: 6px;"><strong>Equipment:</strong> Pro-grade paddles and tournament balls are available at the front desk.</li>
                                    <li><strong>Amenities:</strong> Changing rooms, shower facilities, and lounge refreshments are available for players.</li>
                                </ul>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer Section -->
                    <tr>
                        <td style="background-color: #1e232a; padding: 24px 30px; text-align: center; border-top: 1px solid #2d3748;">
                            <p style="margin: 0 0 8px 0; color: #f7fafc; font-size: 13px; font-weight: 700;">
                                {{ $settings->venue_name ?? 'Paddle Field Sports Center' }}
                            </p>
                            <p style="margin: 0 0 12px 0; color: #a0aec0; font-size: 11px; line-height: 1.5;">
                                {{ $settings->address ?? 'Bacal 3, Talavera, Nueva Ecija' }} • Phone: {{ $settings->phone ?? '+63 917 555 7233' }}
                                @if(!empty($settings->email))
                                    • Email: <a href="mailto:{{ $settings->email }}" style="color: #22d3ee; text-decoration: none;">{{ $settings->email }}</a>
                                @endif
                            </p>
                            <p style="margin: 0; color: #718096; font-size: 10px;">
                                This is an automated reservation confirmation email sent to {{ $booking->customer_email ?: ($booking->user?->email) }}.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
