# Forgot Password Implementation Guide

## Overview
I have successfully implemented a complete forgot password feature for the LGU Document Tracking system. This implementation provides a secure and user-friendly password recovery flow.

## Features Implemented

### 1. **Frontend Updates**
- Added "Forgot your password?" link on the login page
- Created a dedicated forgot password page for email submission
- Created a reset password page with password validation and visibility toggle
- Responsive design consistent with existing UI

### 2. **Backend Components**

#### New Controller: `PasswordResetController`
Located at: `app/Http/Controllers/PasswordResetController.php`

Methods:
- `showForgotPasswordForm()` - Display forgot password form
- `sendResetLink(Request $request)` - Send password reset email
- `showResetForm($token)` - Display reset form with token validation
- `resetPassword(Request $request)` - Update user password

#### New Mailable: `ResetPasswordMail`
Located at: `app/Mail/ResetPasswordMail.php`
- Sends professional password reset email with secure token
- Includes expiration information (1 hour)
- Security reminders in email content

#### New Email Template
Located at: `resources/views/emails/reset-password.blade.php`
- Professional HTML email format
- Clear instructions and CTA button
- Security warnings

### 3. **Database**
- Uses existing `password_reset_tokens` table (created in base migration)
- Stores email, hashed token, and creation timestamp
- 1-hour expiration for security

### 4. **Routes**
All routes in `routes/web.php` within the `guest` middleware group:

```
GET    /forgot-password                   → password.request
POST   /forgot-password                   → password.email
GET    /reset-password/{token}            → password.reset
POST   /reset-password                    → password.update
```

### 5. **Security Features**

#### Rate Limiting
- Forgot password: 3 attempts per hour per email+IP
- Reset password: 5 attempts per hour per IP
- Prevents brute force attacks

#### Token Security
- Tokens are randomly generated (64 characters)
- Stored hashed in database (never plain text)
- 1-hour expiration period
- Single-use tokens (deleted after use)

#### Validation
- Email must exist in system
- Account must be verified (not pending)
- Password must meet Laravel's Password::defaults() rules:
  - Minimum 8 characters
  - At least one uppercase letter
  - At least one lowercase letter
  - At least one number
  - At least one special character
- Password confirmation required
- Invalid/expired tokens rejected

## Environment Configuration

For email sending to work, configure these in your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="LGU Document Tracking"
```

**For Development/Testing:** You can use Mailpit (configured in env.production.template)
```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

## User Flow

### Forgot Password Flow:
1. User clicks "Forgot your password?" on login page
2. User enters their email address
3. System validates email exists and account is verified
4. Password reset link sent to email
5. User receives email with reset button
6. User clicks link in email → redirected to reset form
7. User enters new password (with confirmation)
8. Password updated, token deleted
9. User redirected to login with success message

### Error Handling:
- Non-existent email: Shows friendly error
- Unverified account: Shows friendly error
- Expired token: Redirects to forgot password page with error
- Invalid token: Rejected with error message
- Rate limit exceeded: Shows rate limit error

## Testing the Feature

### Manual Testing:

1. **Test Forgot Password Request:**
   - Navigate to `http://localhost/forgot-password`
   - Enter an email (must be in the database and account must be verified)
   - Should see success message

2. **Test Email Sending:**
   - Check email inbox (or Mailpit if using local dev)
   - Verify email contains:
     - Proper greeting with user name
     - Clear reset password button/link
     - Expiration time (1 hour)
     - Security warnings

3. **Test Token Validation:**
   - Click reset link in email (or manually construct: `/reset-password/{token}`)
   - Should show reset form if token is valid
   - Form should have email, password, and confirmation fields

4. **Test Password Reset:**
   - Enter email and new password (must meet requirements)
   - Submit form
   - Should see success message
   - Old password should no longer work
   - New password should work for login

5. **Test Error Cases:**
   - Try resetting with unverified account
   - Try using expired token (after 1 hour)
   - Try using invalid token
   - Try weak passwords
   - Try mismatched password confirmation
   - Try rate limiting (more than 3 attempts in 1 hour)

### Artisan Commands:

Check registered routes:
```bash
php artisan route:list --name password
```

Clear token table if needed:
```bash
php artisan tinker
# In tinker:
DB::table('password_reset_tokens')->truncate();
```

## File Structure

```
app/
├── Http/
│   └── Controllers/
│       └── PasswordResetController.php (NEW)
├── Mail/
│   └── ResetPasswordMail.php (NEW)
└── Providers/
    └── RouteServiceProvider.php (UPDATED)

resources/views/
├── auth/
│   ├── login.blade.php (UPDATED - added forgot link)
│   ├── forgot-password.blade.php (NEW)
│   └── reset-password.blade.php (NEW)
└── emails/
    └── reset-password.blade.php (NEW)

routes/
└── web.php (UPDATED - added password reset routes)
```

## Key Implementation Details

### Token Storage Strategy
- Plain token sent to user via email
- Hashed token stored in database
- On reset, plain token from URL compared against hashed db value
- This prevents database breach from compromising tokens

### Password Validation
Uses Laravel's built-in `Password` rule which enforces:
- Minimum length of 8 characters
- At least 1 uppercase character
- At least 1 lowercase character  
- At least 1 numeric character
- At least 1 special character (non-alphanumeric)

### Account Verification Check
Only accounts with `status = 'verified'` can reset passwords. This prevents:
- Unverified users from bypassing admin verification
- Deleted/rejected users from regaining access

## Troubleshooting

**Emails not sending:**
1. Check `.env` mail configuration
2. Verify SMTP credentials
3. Check `storage/logs/laravel.log` for errors
4. Use `php artisan tinker` to test: `Mail::to('test@example.com')->send(new ResetPasswordMail($user, $token))`

**Token validation errors:**
1. Ensure token link is intact (check URL encoding)
2. Check token expiration (1 hour limit)
3. Verify token wasn't already used

**Password validation errors:**
1. Ensure password meets all requirements (8+ chars, uppercase, lowercase, number, special char)
2. Verify password confirmation matches

**Rate limiting:**
1. Clear cache if needed: `php artisan cache:clear`
2. Check RateLimiter configuration in RouteServiceProvider

## Security Considerations

✅ **Implemented:**
- Token expiration (1 hour)
- Token hashing before storage
- CSRF protection
- Rate limiting
- Account verification check
- Single-use tokens
- Password strength requirements
- Secure email verification

✅ **Best Practices:**
- Tokens are cryptographically secure random
- No password reset before account verification
- Clear security warnings in emails
- Rate limiting prevents brute force
- Expired tokens cannot be reused
- No username/email enumeration (safe error messages)

## Database Schema

The implementation uses the existing `password_reset_tokens` table:

```sql
CREATE TABLE password_reset_tokens (
  email VARCHAR(255) PRIMARY KEY,
  token VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL
);
```

## Future Enhancements

Potential improvements:
- SMS-based password reset
- Two-factor authentication with password reset
- Admin override capability
- Password reset history/audit logging
- Custom password complexity rules
- Temporary one-time passwords
- Integration with OAuth providers

## Support & Maintenance

If you encounter issues:
1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Verify database migrations ran: `php artisan migrate:status`
3. Clear cache: `php artisan cache:clear`
4. Check email configuration in `.env`
5. Review rate limiter settings in `RouteServiceProvider`
