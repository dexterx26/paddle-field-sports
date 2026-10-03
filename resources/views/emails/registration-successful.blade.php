<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $settings->venue_name ?? 'Paddle Field Sports Center' }}</title>
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
                                        <!-- Brand Badge -->
                                        <div style="display: inline-block; width: 56px; height: 56px; line-height: 56px; border-radius: 16px; background: linear-gradient(135deg, #b88250 0%, #0891b2 100%); color: #ffffff; font-size: 26px; font-weight: bold; text-align: center; margin-bottom: 12px; box-shadow: 0 4px 12px rgba(8, 145, 178, 0.35);">
                                            🏓
                                        </div>
                                        <h1 style="margin: 0; color: #f7fafc; font-size: 22px; font-weight: 800; letter-spacing: -0.5px; text-transform: uppercase;">
                                            {{ $settings->venue_name ?? 'Paddle Field Sports Center' }}
                                        </h1>
                                        <p style="margin: 4px 0 0 0; color: #d4a373; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px;">
                                            Pickleball Arena & Clubhouse • Bacal 3, Talavera
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Welcome Body Section -->
                    <tr>
                        <td style="padding: 36px 32px;">
                            @if(!empty($verificationUrl) && !$user->hasVerifiedEmail())
                                <!-- Verification Banner Pill -->
                                <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px;">
                                    <p style="margin: 0; color: #b45309; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                        ⚠️ Action Required: Email Confirmation
                                    </p>
                                    <p style="margin: 4px 0 0 0; color: #78350f; font-size: 13px;">
                                        Please confirm your email address to activate your account before you can log in.
                                    </p>
                                </div>

                                <h2 style="margin: 0 0 12px 0; color: #1e232a; font-size: 22px; font-weight: 800;">
                                    Hello, {{ $user->name }}! 🏓
                                </h2>
                                <p style="margin: 0 0 20px 0; font-size: 14px; color: #596579; line-height: 1.6;">
                                    Thank you for registering at <strong>{{ $settings->venue_name ?? 'Paddle Field Sports Center' }}</strong>. Before you can log in and book court slots, please confirm that this is your email address by clicking the button below:
                                </p>

                                <!-- Prominent Verification CTA Button -->
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 24px 0;">
                                    <tr>
                                        <td align="center">
                                            <a href="{{ $verificationUrl }}" target="_blank" style="display: inline-block; padding: 16px 36px; font-size: 14px; font-weight: 800; color: #12151b; background: linear-gradient(135deg, #0891b2 0%, #22d3ee 100%); text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px; border-radius: 12px; box-shadow: 0 4px 14px rgba(8, 145, 178, 0.4);">
                                                ✓ Confirm Email Address
                                            </a>
                                            <p style="margin: 10px 0 0 0; font-size: 11px; color: #888888;">
                                                This confirmation link expires in 24 hours.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <!-- Welcome Banner Pill -->
                                <div style="background-color: #ecfeff; border-left: 4px solid #0891b2; padding: 12px 18px; border-radius: 8px; margin-bottom: 24px;">
                                    <p style="margin: 0; color: #0891b2; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                        ✓ Registration Confirmed & Account Active
                                    </p>
                                </div>

                                <h2 style="margin: 0 0 12px 0; color: #1e232a; font-size: 22px; font-weight: 800;">
                                    Hello, {{ $user->name }}! 🎉
                                </h2>
                                <p style="margin: 0 0 20px 0; font-size: 14px; color: #596579; line-height: 1.6;">
                                    Welcome to <strong>Paddle Field Sports Center</strong>! Your player account has been successfully created. You can now effortlessly reserve court slots in real-time, view live schedules, manage holds, and participate in competitive club matches.
                                </p>
                            @endif

                            <!-- Account Details Table Card -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #faf6f0; border-radius: 14px; border: 1px solid #eae3d7; margin: 24px 0; overflow: hidden;">
                                <tr>
                                    <td colspan="2" style="background-color: #efe7dc; padding: 12px 18px; border-bottom: 1px solid #e2dbcf;">
                                        <strong style="color: #1e232a; font-size: 12px; text-transform: uppercase; letter-spacing: 0.8px;">
                                            📋 Your Account Information
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #596579; border-bottom: 1px solid #eae3d7; width: 35%;">
                                        <strong>Full Name</strong>
                                    </td>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #1e232a; font-weight: 600; border-bottom: 1px solid #eae3d7;">
                                        {{ $user->name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #596579; border-bottom: 1px solid #eae3d7;">
                                        <strong>Email Address</strong>
                                    </td>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #0891b2; font-weight: 600; border-bottom: 1px solid #eae3d7;">
                                        {{ $user->email }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #596579; border-bottom: 1px solid #eae3d7;">
                                        <strong>Phone Number</strong>
                                    </td>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #1e232a; font-weight: 600; border-bottom: 1px solid #eae3d7;">
                                        {{ $user->phone ?? 'Not provided (Linked via Social)' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #596579; border-bottom: 1px solid #eae3d7;">
                                        <strong>Account Role</strong>
                                    </td>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #b88250; font-weight: 700; text-transform: capitalize; border-bottom: 1px solid #eae3d7;">
                                        {{ str_replace('_', ' ', $user->role) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #596579;">
                                        <strong>Registered On</strong>
                                    </td>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #1e232a; font-weight: 600;">
                                        {{ $user->created_at ? $user->created_at->format('M d, Y - h:i A') : now()->format('M d, Y - h:i A') }}
                                    </td>
                                </tr>
                            </table>

                            <!-- Call to Action Buttons -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0 16px 0;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                            <tr>
                                                <td align="center" style="border-radius: 12px; background: linear-gradient(135deg, #0891b2 0%, #22d3ee 100%); padding: 0 4px;">
                                                    <a href="{{ $reserveUrl }}" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 13px; font-weight: 800; color: #12151b; text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px; border-radius: 10px;">
                                                        🎾 Book a Court Now
                                                    </a>
                                                </td>
                                                <td style="width: 12px;"></td>
                                                <td align="center" style="border-radius: 12px; background-color: #efe7dc; border: 1px solid #d4a373; padding: 0 4px;">
                                                    <a href="{{ $profileUrl }}" target="_blank" style="display: inline-block; padding: 14px 22px; font-size: 13px; font-weight: 700; color: #b88250; text-decoration: none; border-radius: 10px;">
                                                        ⚙️ View Profile
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- What You Can Do Section -->
                            <div style="margin-top: 30px; padding-top: 24px; border-top: 1px dashed #e2dbcf;">
                                <h3 style="margin: 0 0 12px 0; color: #1e232a; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                    ⭐ Member Benefits
                                </h3>
                                <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #596579; line-height: 1.8;">
                                    <li><strong>Instant Real-time Holds:</strong> Lock your preferred court slots for 2 minutes while completing payment.</li>
                                    <li><strong>Flexible Payment Options:</strong> Pay instantly via PayMongo (GCash, Maya, QR Ph, Cards) or upload direct transfer receipts.</li>
                                    <li><strong>Profile Customization:</strong> Update your contact number, upload an avatar, and update passwords securely anytime.</li>
                                </ul>
                            </div>

                            <!-- Facility Details Card -->
                            <div style="margin-top: 24px; background-color: #1e232a; border-radius: 14px; padding: 20px 24px; color: #f7fafc;">
                                <h4 style="margin: 0 0 10px 0; color: #22d3ee; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                    📍 Facility Location & Operating Hours
                                </h4>
                                <p style="margin: 0 0 6px 0; font-size: 12px; color: #cccccc;">
                                    <strong>Address:</strong> {{ $settings->address ?? 'Bacal 3, Talavera, Nueva Ecija' }}
                                </p>
                                <p style="margin: 0 0 6px 0; font-size: 12px; color: #cccccc;">
                                    <strong>Hours:</strong> Open Daily from {{ $settings->opening_time ?? '06:00' }} to {{ $settings->closing_time ?? '00:00' }} Midnight
                                </p>
                                <p style="margin: 0; font-size: 12px; color: #cccccc;">
                                    <strong>Contact Hotline:</strong> {{ $settings->phone ?? '+63 917 555 7233' }}
                                </p>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer Section -->
                    <tr>
                        <td style="background-color: #efe7dc; padding: 24px 30px; text-align: center; border-top: 1px solid #e2dbcf; font-size: 11px; color: #596579; line-height: 1.6;">
                            <p style="margin: 0 0 8px 0; font-weight: 600; color: #1e232a;">
                                {{ $settings->venue_name ?? 'Paddle Field Sports Center' }} &copy; {{ date('Y') }}. All rights reserved.
                            </p>
                            <p style="margin: 0;">
                                This is an automated email confirmation sent to <strong>{{ $user->email }}</strong> upon account registration.<br>
                                If you did not create this account, please disregard or reach out to our team at {{ $settings->email ?? 'contact@paddlefield.com' }}.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
