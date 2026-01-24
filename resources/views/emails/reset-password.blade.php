<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f4f4f4; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { background-color: #fff; padding: 20px; border: 1px solid #ddd; }
        .footer { background-color: #f4f4f4; padding: 20px; border-radius: 0 0 5px 5px; text-align: center; font-size: 12px; }
        .button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .warning { color: #d9534f; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Password Reset Request</h2>
        </div>
        <div class="content">
            <p>Hi {{ $user->name }},</p>
            
            <p>We received a request to reset your password for your LGU Document Tracking System account.</p>
            
            <p><a href="{{ $resetUrl }}" class="button">Reset Password</a></p>
            
            <p><strong>This link will expire in 1 hour.</strong></p>
            
            <p>If you did not request a password reset, please ignore this email. Your password will remain unchanged.</p>
            
            <h4>Important Security Notes:</h4>
            <ul>
                <li>Never share this link with anyone</li>
                <li>Only click this link if you requested the password reset</li>
                <li>If you believe your account has been compromised, contact an administrator immediately</li>
            </ul>
            
            <p><strong>Can't click the button?</strong><br>
            Copy and paste this link in your browser:<br>
            <a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>
        </div>
        <div class="footer">
            <p>&copy; LGU Document Tracking System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
