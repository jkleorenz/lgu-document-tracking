<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f4f4f4; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { background-color: #fff; padding: 20px; border: 1px solid #ddd; }
        .footer { background-color: #f4f4f4; padding: 20px; border-radius: 0 0 5px 5px; text-align: center; font-size: 12px; }
        .otp-box { background-color: #e8f4f8; padding: 20px; text-align: center; border-radius: 5px; margin: 20px 0; }
        .otp-code { font-size: 32px; font-weight: bold; color: #007bff; letter-spacing: 5px; }
        .warning { color: #d9534f; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Password Reset - One Time Password (OTP)</h2>
        </div>
        <div class="content">
            <p>Hi {{ $user->name }},</p>
            
            <p>We received a request to reset your password for your LGU Document Tracking System account.</p>
            
            <p>Your One-Time Password (OTP) is:</p>
            
            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
            </div>
            
            <p><strong>Important:</strong></p>
            <ul>
                <li>This OTP will expire in <span class="warning">10 minutes</span></li>
                <li>Never share this OTP with anyone</li>
                <li>Only use this OTP if you requested a password reset</li>
                <li>If you did not request this, please ignore this email</li>
                <li>You have only 3 attempts to enter the correct OTP</li>
            </ul>
            
            <p>If you did not request a password reset, please ignore this email and your password will remain unchanged.</p>
            
            <p>If you believe your account has been compromised, please contact an administrator immediately.</p>
        </div>
        <div class="footer">
            <p>&copy; LGU Document Tracking System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
