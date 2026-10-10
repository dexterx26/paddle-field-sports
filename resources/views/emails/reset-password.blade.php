<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password – {{ $settings->venue_name ?? 'Paddle Field Sports Center' }}</title>
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

                    <!-- Reset Password Body Section -->
                    <tr>
                        <td style="padding: 36px 32px;">
                            <!-- Alert Pill -->
                            <div style="background-color: #ecfeff; border-left: 4px solid #0891b2; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px;">
                                <p style="margin: 0; color: #0891b2; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                    🔐 Password Reset Request
                                </p>
                                <p style="margin: 4px 0 0 0; color: #164e63; font-size: 13px;">
                                    We received a request to reset the password for your Paddle Field account.
                                </p>
                            </div>

                            <h2 style="margin: 0 0 12px 0; color: #1e232a; font-size: 22px; font-weight: 800;">
                                Hello, {{ $user->name }}!
                            </h2>
                            <p style="margin: 0 0 20px 0; font-size: 14px; color: #596579; line-height: 1.6;">
                                You can reset your password by clicking the button below. This link will expire in <strong>60 minutes</strong> for your security.
                            </p>

                            <!-- Prominent Reset CTA Button -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $resetUrl }}" target="_blank" style="display: inline-block; padding: 16px 36px; font-size: 14px; font-weight: 800; color: #12151b; background: linear-gradient(135deg, #0891b2 0%, #22d3ee 100%); text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px; border-radius: 12px; box-shadow: 0 4px 14px rgba(8, 145, 178, 0.4);">
                                            Reset My Password &rarr;
                                        </a>
                                        <p style="margin: 10px 0 0 0; font-size: 11px; color: #888888;">
                                            This link is single-use and expires in 60 minutes.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Account Details Table Card -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #faf6f0; border-radius: 14px; border: 1px solid #eae3d7; margin: 24px 0; overflow: hidden;">
                                <tr>
                                    <td colspan="2" style="background-color: #efe7dc; padding: 12px 18px; border-bottom: 1px solid #e2dbcf;">
                                        <strong style="color: #1e232a; font-size: 12px; text-transform: uppercase; letter-spacing: 0.8px;">
                                            📋 Account Details
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #596579; border-bottom: 1px solid #eae3d7; width: 35%;">
                                        <strong>Account Name</strong>
                                    </td>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #1e232a; font-weight: 600; border-bottom: 1px solid #eae3d7;">
                                        {{ $user->name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #596579;">
                                        <strong>Registered Email</strong>
                                    </td>
                                    <td style="padding: 12px 18px; font-size: 13px; color: #0891b2; font-weight: 600;">
                                        {{ $user->email }}
                                    </td>
                                </tr>
                            </table>

                            <!-- Security Notice Callout -->
                            <div style="background-color: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px; margin: 24px 0;">
                                <p style="margin: 0; font-size: 12px; color: #64748b; line-height: 1.5;">
                                    🔒 <strong>Did not request a password reset?</strong> If you did not make this request, you can safely ignore this email. Your password will remain unchanged and your account is secure.
                                </p>
                            </div>

                            <!-- Fallback Link -->
                            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2dbcf; font-size: 12px; color: #888888; word-break: break-all;">
                                <p style="margin: 0 0 6px 0;">If the button above doesn't work, copy and paste this URL into your browser:</p>
                                <a href="{{ $resetUrl }}" style="color: #0891b2; text-decoration: underline;">{{ $resetUrl }}</a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #1e232a; padding: 24px 30px; text-align: center; border-top: 1px solid #333d4b;">
                            <p style="margin: 0; color: #94a3b8; font-size: 12px;">
                                &copy; {{ date('Y') }} {{ $settings->venue_name ?? 'Paddle Field Sports Center' }}. All rights reserved.
                            </p>
                            <p style="margin: 6px 0 0 0; color: #64748b; font-size: 11px;">
                                Bacal 3, Talavera, Nueva Ecija • Official Court Booking System
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
