<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your BNT Membership Card</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f5f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f5f7; padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Email Container -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 30px 40px 20px 40px;">
                            <h2 style="margin: 0 0 10px 0; color: #1f2937; font-size: 24px; font-weight: 700; text-align: center;">Welcome to the Trust!</h2>
                            <p style="margin: 0; color: #4b5563; font-size: 14px; line-height: 1.5; text-align: center;">Thank you for supporting the Bahamas National Trust. Your digital membership card is ready below.</p>
                        </td>
                    </tr>

                    <!-- Card Body -->
                    <tr>
                        <td align="center" style="padding: 10px 30px 20px 30px;">
                            <!-- The Membership Card -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background: linear-gradient(135deg, #00522b 0%, #006838 100%); border-radius: 16px; box-shadow: 0 8px 24px rgba(0, 104, 56, 0.25); color: #ffffff; text-align: left; overflow: hidden; position: relative;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <!-- Logo & Header -->
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-bottom: 1px solid rgba(255, 255, 255, 0.15); padding-bottom: 12px;">
                                            <tr>
                                                <td>
                                                    <span style="font-size: 12px; font-weight: 700; letter-spacing: 2px; color: #d4af37; text-transform: uppercase;">Bahamas National Trust</span>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Name & ID -->
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 24px;">
                                            <tr>
                                                <td>
                                                    <div style="font-size: 20px; font-weight: 700; margin-bottom: 4px; letter-spacing: 0.5px;">{{ $member->name }}</div>
                                                    <div style="font-size: 12px; color: rgba(255, 255, 255, 0.7); letter-spacing: 1px;">MEMBER ID: <strong style="color: #ffffff;">{{ $member->membership_number }}</strong></div>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Footer Info -->
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 30px;">
                                            <tr>
                                                <td width="60%" valign="bottom">
                                                    <div style="font-size: 10px; color: rgba(255, 255, 255, 0.6); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Membership Tier</div>
                                                    <div style="font-size: 14px; font-weight: 700; color: #d4af37; text-transform: uppercase; letter-spacing: 1px;">{{ $member->tier }}</div>
                                                    
                                                    <div style="font-size: 10px; color: rgba(255, 255, 255, 0.6); text-transform: uppercase; letter-spacing: 1px; margin-top: 16px; margin-bottom: 4px;">Expires</div>
                                                    <div style="font-size: 13px; font-weight: 600;">{{ $member->expires_at ? $member->expires_at->format('M d, Y') : 'NEVER' }}</div>
                                                </td>
                                                <td width="40%" align="right" valign="bottom">
                                                    <!-- Dynamic QR Code -->
                                                    <div style="background-color: #ffffff; padding: 6px; border-radius: 8px; display: inline-block;">
                                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ urlencode(route('members.pass.apple', $member->uuid)) }}" alt="QR Code" width="90" height="90" style="display: block; border: 0;">
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Wallet Badges -->
                    <tr>
                        <td align="center" style="padding: 20px 30px 40px 30px; border-top: 1px solid #f3f4f6;">
                            <p style="margin: 0 0 15px 0; color: #4b5563; font-size: 13px; font-weight: 500;">Add to your mobile device for offline entry:</p>
                            
                            <!-- Apple Wallet Button -->
                            <a href="{{ route('members.pass.apple', $member->uuid) }}" style="display: block; max-width: 250px; background-color: #000000; border: 1.5px solid #a3a3a3; border-radius: 8px; padding: 10px 16px; text-decoration: none; margin-bottom: 12px; transition: transform 0.2s;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td width="24" align="left">
                                            <!-- Simple inline svg mimicking apple wallet logo -->
                                            <span style="font-size: 20px; vertical-align: middle;">🍏</span>
                                        </td>
                                        <td align="left" style="padding-left: 8px; color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, sans-serif; font-size: 14px; font-weight: 600; letter-spacing: 0.3px;">
                                            Add to Apple Wallet
                                        </td>
                                    </tr>
                                </table>
                            </a>

                            <!-- Google Wallet Button -->
                            <a href="{{ $googleWalletUrl }}" style="display: block; max-width: 250px; background-color: #000000; border: 1.5px solid #a3a3a3; border-radius: 8px; padding: 10px 16px; text-decoration: none;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td width="24" align="left">
                                            <span style="font-size: 20px; vertical-align: middle;">💳</span>
                                        </td>
                                        <td align="left" style="padding-left: 8px; color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, sans-serif; font-size: 14px; font-weight: 600; letter-spacing: 0.3px;">
                                            Save to Google Wallet
                                        </td>
                                    </tr>
                                </table>
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
