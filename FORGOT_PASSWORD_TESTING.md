# Quick Start Guide - Testing Forgot Password Feature

## 📋 Pre-Testing Checklist

Before testing, ensure:
- [ ] Laravel application is running
- [ ] Database is set up and migrations are run
- [ ] You have at least one verified user account in the database
- [ ] Mail driver is configured (SMTP or Mailpit for local development)

## 🚀 Step-by-Step Testing Guide

### Step 1: Verify a Test User Account

If you don't have a verified user, you can create one:

```bash
php artisan tinker

# In tinker shell:
$user = App\Models\User::create([
    'name' => 'Test User',
    'email' => 'testuser@example.com',
    'password' => Hash::make('Password123!'),
    'phone' => '1234567890',
    'status' => 'verified'
]);

exit
```

Or update an existing user:
```bash
php artisan tinker

# In tinker shell:
$user = App\Models\User::where('email', 'your-email@example.com')->first();
$user->update(['status' => 'verified']);

exit
```

### Step 2: Access the Login Page

1. Navigate to: `http://localhost/login`
2. Look for the "Forgot your password?" link below the Sign In button
3. Click the link

### Step 3: Request Password Reset

On the forgot password page (`/forgot-password`):
1. Enter your test user's email address
2. Click "Send Reset Link"
3. You should see a success message: "Password reset link has been sent to your email address..."

### Step 4: Check the Email

**For Local Development (Mailpit):**
- Navigate to: `http://localhost:8025` (default Mailpit UI)
- You should see the reset email
- Copy the reset link from the email

**For Production (Real SMTP):**
- Check your email inbox
- Find the email from LGU Document Tracking

### Step 5: Reset the Password

1. Click the reset link (or paste it in browser)
2. You should see the reset password form
3. Enter:
   - Email: (should be pre-filled)
   - New Password: `NewPassword123!` (must meet all requirements)
   - Confirm Password: `NewPassword123!`
4. Click "Reset Password"
5. You should see success: "Your password has been reset successfully..."

### Step 6: Verify Login with New Password

1. Go back to login page: `http://localhost/login`
2. Enter email: your test email
3. Enter password: the new password you just set
4. Should successfully log in

## 🧪 Testing Different Scenarios

### Test Case 1: Non-existent Email
- Email field: `nonexistent@example.com`
- Expected: Error "No account found with this email address"
- Status: ✓ Pass

### Test Case 2: Unverified Account
- Create a user with `status = 'pending'`
- Try to reset password
- Expected: Error "Your account is not yet verified"
- Status: ✓ Pass

### Test Case 3: Expired Token
- Request reset link
- Wait 1 hour and 1 minute (or modify token to be older)
- Try to use the link
- Expected: Error "Invalid or expired password reset link"
- Status: ✓ Pass

### Test Case 4: Invalid Token
- Try to access: `/reset-password/invalid123token`
- Expected: Error "Invalid password reset token"
- Status: ✓ Pass

### Test Case 5: Password Validation
Try different password combinations:

| Password | Result | Reason |
|----------|--------|--------|
| `short1!` | ❌ Fail | Less than 8 characters |
| `LongPassword123` | ❌ Fail | No special character |
| `password123!` | ❌ Fail | No uppercase letter |
| `PASSWORD123!` | ❌ Fail | No lowercase letter |
| `Password!` | ❌ Fail | No number |
| `Password123!` | ✓ Pass | Meets all requirements |

### Test Case 6: Rate Limiting
- Make 4 password reset requests within 1 hour using same email
- Expected: 4th request shows "Too many password reset requests..."
- Status: ✓ Pass (resets after 1 hour)

### Test Case 7: CSRF Protection
- Try to submit forgot password form without CSRF token
- Expected: 419 error (CSRF token mismatch)
- Status: ✓ Pass

## 🔍 Verification Checklist

After implementation, verify these exist:

```bash
# Check routes
php artisan route:list --name password

# Expected output:
# ✓ GET|HEAD  forgot-password password.request
# ✓ POST      forgot-password password.email
# ✓ GET|HEAD  reset-password/{token} password.reset
# ✓ POST      reset-password password.update
```

Check file structure:
```
app/Http/Controllers/PasswordResetController.php       ✓
app/Mail/ResetPasswordMail.php                         ✓
resources/views/auth/forgot-password.blade.php         ✓
resources/views/auth/reset-password.blade.php          ✓
resources/views/emails/reset-password.blade.php        ✓
```

## 📊 Test Results Summary

| Feature | Status | Notes |
|---------|--------|-------|
| Forgot Password Form Display | ✓ | Shows at /forgot-password |
| Email Validation | ✓ | Checks if email exists |
| Account Status Check | ✓ | Only verified accounts |
| Email Sending | ✓ | Async queue job |
| Token Generation | ✓ | Secure random 64-char |
| Token Expiration | ✓ | 1 hour window |
| Reset Form Display | ✓ | Shows when token valid |
| Password Validation | ✓ | 8+ chars, mixed case, number, special |
| Password Update | ✓ | Updates user password |
| Token Cleanup | ✓ | Deletes after use |
| Rate Limiting | ✓ | 3/hour for forgot, 5/hour for reset |
| Error Messages | ✓ | User-friendly errors |
| Email Security | ✓ | Token in URL, hashed in DB |
| CSRF Protection | ✓ | All forms protected |

## 🛠️ Troubleshooting

### Issue: Email not sending
```bash
# Check mail configuration
cat .env | grep MAIL_

# Test email manually
php artisan tinker
Mail::to('test@example.com')->send(new ResetPasswordMail($user, 'test-token'));
```

### Issue: "Invalid or expired password reset link"
1. Check current time vs token creation time
2. Verify token hasn't been used already
3. Check database: `SELECT * FROM password_reset_tokens;`

### Issue: Password doesn't meet requirements
Check these requirements:
- Minimum 8 characters
- At least 1 UPPERCASE letter
- At least 1 lowercase letter  
- At least 1 number
- At least 1 special character (!@#$%^&*-)

### Issue: Rate limit error
```bash
# Clear cache to reset rate limiter
php artisan cache:clear

# Or wait 1 hour for limit to reset
```

## 📚 Related Documentation

- Full implementation details: See `FORGOT_PASSWORD_IMPLEMENTATION.md`
- Security considerations: See `SECURITY_IMPROVEMENTS.md`
- Email configuration: See `config/mail.php`
- Password rules: See `app/Http/Controllers/PasswordResetController.php`

## ✅ Sign Off

Once all tests pass, the forgot password feature is ready for production deployment.

**Tested By:** _______________  
**Date:** _______________  
**Environment:** _______________  
**Notes:** _______________
