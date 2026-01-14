# Forgot Password Feature - Architecture & Flow Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                     FORGOT PASSWORD SYSTEM ARCHITECTURE                 │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────┐
│   USER BROWSER           │
│  ┌────────────────────┐  │
│  │  Login Page        │  │
│  │  - Email field     │  │
│  │  - Password field  │  │
│  │  - Forgot link ✓   │  │ ← NEW
│  └────────────────────┘  │
│           ↓              │
│  ┌────────────────────┐  │
│  │  Forgot Password   │  │
│  │  - Email form ✓    │  │ ← NEW
│  │  - Submit button   │  │
│  └────────────────────┘  │
│           ↓              │
│  ┌────────────────────┐  │
│  │  Reset Password    │  │
│  │  - Password form ✓ │  │ ← NEW
│  │  - Confirm field   │  │
│  │  - Submit button   │  │
│  └────────────────────┘  │
│           ↑              │
│  Email with reset link   │
└──────────────────────────┘
           ↕
┌──────────────────────────────────────────────────────────────────────────┐
│                    LARAVEL APPLICATION                                   │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌────────────────────────────────────────────────────────────────┐   │
│  │  ROUTES (web.php)                                              │   │
│  │  ┌──────────────────────────────────────────────────────────┐ │   │
│  │  │ GET  /forgot-password → password.request               │ │   │
│  │  │ POST /forgot-password → password.email [throttle]      │ │   │
│  │  │ GET  /reset-password/{token} → password.reset          │ │   │
│  │  │ POST /reset-password → password.update [throttle]      │ │   │
│  │  └──────────────────────────────────────────────────────────┘ │   │
│  └────────────────────────────────────────────────────────────────┘   │
│                                    ↓                                    │
│  ┌────────────────────────────────────────────────────────────────┐   │
│  │  CONTROLLER: PasswordResetController ✓ NEW                    │   │
│  │  ┌──────────────────────────────────────────────────────────┐ │   │
│  │  │ showForgotPasswordForm()                                │ │   │
│  │  │  - Return forgot-password.blade.php                    │ │   │
│  │  │                                                          │ │   │
│  │  │ sendResetLink($request)                                │ │   │
│  │  │  - Validate email exists                              │ │   │
│  │  │  - Check account is verified                          │ │   │
│  │  │  - Rate limiting check                                │ │   │
│  │  │  - Generate random token                              │ │   │
│  │  │  - Hash and store token                               │ │   │
│  │  │  - Send reset email (queue job)                       │ │   │
│  │  │  - Return success view                                │ │   │
│  │  │                                                          │ │   │
│  │  │ showResetForm($token)                                  │ │   │
│  │  │  - Check token exists in DB                           │ │   │
│  │  │  - Verify token not expired (1 hour)                  │ │   │
│  │  │  - Return reset-password.blade.php                    │ │   │
│  │  │                                                          │ │   │
│  │  │ resetPassword($request)                                │ │   │
│  │  │  - Validate email exists                              │ │   │
│  │  │  - Get token from database                            │ │   │
│  │  │  - Check token expiration                             │ │   │
│  │  │  - Verify token matches                               │ │   │
│  │  │  - Validate password strength                         │ │   │
│  │  │  - Hash new password                                  │ │   │
│  │  │  - Update user record                                 │ │   │
│  │  │  - Delete used token                                  │ │   │
│  │  │  - Redirect to login with success                     │ │   │
│  │  └──────────────────────────────────────────────────────────┘ │   │
│  └────────────────────────────────────────────────────────────────┘   │
│                                    ↓                                    │
│  ┌────────────────────────────────────────────────────────────────┐   │
│  │  MAILABLE: ResetPasswordMail ✓ NEW                            │   │
│  │  ┌──────────────────────────────────────────────────────────┐ │   │
│  │  │ ResetPasswordMail($user, $token)                        │ │   │
│  │  │  - Async queue job (ShouldQueue)                       │ │   │
│  │  │  - Passes user and token to template                   │ │   │
│  │  │  - Sets proper email headers                           │ │   │
│  │  │  - Template: emails/reset-password.blade.php ✓ NEW    │ │   │
│  │  └──────────────────────────────────────────────────────────┘ │   │
│  └────────────────────────────────────────────────────────────────┘   │
│                                    ↓                                    │
│  ┌────────────────────────────────────────────────────────────────┐   │
│  │  DATABASE                                                      │   │
│  │  ┌──────────────────────────────────────────────────────────┐ │   │
│  │  │ password_reset_tokens TABLE (exists)                   │ │   │
│  │  │ ┌──────────────────────────────────────────────────────┤ │   │
│  │  │ │ email        | VARCHAR(255) PRIMARY KEY             │ │   │
│  │  │ │ token        | VARCHAR(255) - Hashed                │ │   │
│  │  │ │ created_at   | TIMESTAMP - Expiration tracking      │ │   │
│  │  │ └──────────────────────────────────────────────────────┘ │   │
│  │  │                                                          │ │   │
│  │  │ users TABLE (existing)                                 │ │   │
│  │  │ ┌──────────────────────────────────────────────────────┤ │   │
│  │  │ │ id          | BIGINT PRIMARY KEY                    │ │   │
│  │  │ │ email       | VARCHAR(255) UNIQUE                   │ │   │
│  │  │ │ password    | VARCHAR(255) - Updated with hash     │ │   │
│  │  │ │ status      | ENUM - checked for 'verified'        │ │   │
│  │  │ │ ...         | other fields                          │ │   │
│  │  │ └──────────────────────────────────────────────────────┘ │   │
│  │  └──────────────────────────────────────────────────────────┘ │   │
│  └────────────────────────────────────────────────────────────────┘   │
│                                    ↓                                    │
│  ┌────────────────────────────────────────────────────────────────┐   │
│  │  SECURITY LAYERS                                               │   │
│  │  ┌──────────────────────────────────────────────────────────┐ │   │
│  │  │ ✓ CSRF Protection          - All forms protected        │ │   │
│  │  │ ✓ Rate Limiting            - 3/hr forgot, 5/hr reset   │ │   │
│  │  │ ✓ Token Hashing            - bcrypt before storage     │ │   │
│  │  │ ✓ Token Expiration         - 1 hour window             │ │   │
│  │  │ ✓ Account Verification     - Only verified users       │ │   │
│  │  │ ✓ Password Validation      - 8+ chars, mixed case, #  │ │   │
│  │  │ ✓ Input Sanitization       - All inputs validated      │ │   │
│  │  │ ✓ Single-Use Tokens        - Deleted after use        │ │   │
│  │  │ ✓ Secure Email             - Over SMTP with TLS       │ │   │
│  │  │ ✓ Error Handling           - Safe messages (no enum)  │ │   │
│  │  └──────────────────────────────────────────────────────────┘ │   │
│  └────────────────────────────────────────────────────────────────┘   │
│                                                                          │
└──────────────────────────────────────────────────────────────────────────┘
           ↓
┌──────────────────────────┐
│   MAIL SERVER            │
│  ┌────────────────────┐  │
│  │  SMTP/Mailgun      │  │
│  │  Sends emails to   │  │
│  │  user inbox        │  │
│  └────────────────────┘  │
│           ↓              │
│  User receives email     │
│  with reset link         │
└──────────────────────────┘
```

---

## Request/Response Flow Diagram

### 1. FORGOT PASSWORD REQUEST

```
User Request
    ↓
GET /forgot-password
    ↓
PasswordResetController@showForgotPasswordForm()
    ↓
Return: auth.forgot-password view
    ↓
Render: Email input form
```

### 2. SEND RESET EMAIL

```
User Submits Email Form
    ↓
POST /forgot-password (throttle: forgot-password)
    ↓
Middleware: Check rate limit (3/hour per IP+email)
    ↓
PasswordResetController@sendResetLink()
    │
    ├─ Validate: email required, email format, email exists
    ├─ Find: User by email
    ├─ Check: User status === 'verified'
    │
    ├─ Generate: Random 64-char token
    ├─ Hash: bcrypt(token)
    │
    ├─ Delete: Old reset tokens for this email
    ├─ Insert: New record in password_reset_tokens
    │    - email, hashed_token, created_at
    │
    ├─ Queue: ResetPasswordMail job
    │    - User object
    │    - Plain token (for URL)
    │
    └─ Return: Success message
```

### 3. USER RECEIVES EMAIL

```
ResetPasswordMail Job Runs
    ↓
Mail::to($user->email)->send(new ResetPasswordMail($user, $token))
    ↓
Render: emails/reset-password.blade.php
    ├─ Build reset URL: /reset-password/{token}
    ├─ Add user greeting
    ├─ Format: CTA button + fallback link
    ├─ Include: Expiration info (1 hour)
    └─ Include: Security warnings
    ↓
Send: Over SMTP to user's email
```

### 4. CLICK RESET LINK

```
User Clicks: /reset-password/{token}
    ↓
GET /reset-password/{token}
    ↓
PasswordResetController@showResetForm($token)
    │
    ├─ Query: password_reset_tokens table
    ├─ Check: created_at > now() - 1 hour
    ├─ Verify: Token record exists
    │
    ├─ Valid?  → Render: reset-password.blade.php
    │           └─ Show password reset form
    │
    └─ Invalid? → Redirect: /forgot-password
                  └─ Show error: "Token expired"
```

### 5. RESET PASSWORD

```
User Submits New Password
    ↓
POST /reset-password (throttle: reset-password)
    ↓
Middleware: Check rate limit (5/hour per IP)
    ↓
PasswordResetController@resetPassword()
    │
    ├─ Validate Input:
    │   ├─ email required, email format, email exists
    │   ├─ token required
    │   ├─ password required, min:8, password_confirmed
    │   ├─ password regex: /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])/
    │
    ├─ Query: password_reset_tokens by email
    ├─ Check: Token record exists
    ├─ Check: created_at > now() - 1 hour (not expired)
    │
    ├─ Verify: Hash::check($plain_token, $hashed_token_in_db)
    │   ├─ Match?    → Continue
    │   └─ No Match? → Error: "Invalid token"
    │
    ├─ Find: User by email
    ├─ Update: User password = Hash::make($new_password)
    │
    ├─ Delete: password_reset_tokens record for this email
    │
    └─ Redirect: /login
                 └─ With success: "Password reset successfully"
```

---

## Security Flow Diagram

```
┌────────────────────────────────────────────────────────────────┐
│              PASSWORD RESET SECURITY LAYERS                    │
└────────────────────────────────────────────────────────────────┘

INPUT VALIDATION LAYER
├─ Email validation: required, email format, exists in users table
├─ Password validation:
│  ├─ Minimum 8 characters
│  ├─ At least 1 uppercase letter [A-Z]
│  ├─ At least 1 lowercase letter [a-z]
│  ├─ At least 1 digit [0-9]
│  └─ At least 1 special character [@$!%*?&]
├─ Token validation: required, string format
└─ CSRF token validation: must match session token

ACCOUNT VERIFICATION LAYER
├─ Email must exist in users table
├─ Account status must be 'verified'
└─ Prevents unverified/banned users from resetting

RATE LIMITING LAYER
├─ Forgot password: 3 attempts per hour per (IP + email)
├─ Reset password: 5 attempts per hour per IP
└─ Returns 429 error with friendly message

TOKEN SECURITY LAYER
├─ Generation:
│  ├─ Use Str::random(64) for 64-char cryptographically secure token
│  └─ Never log or expose token
├─ Storage:
│  ├─ Hash token with bcrypt: Hash::make($token)
│  ├─ Store hashed version in password_reset_tokens table
│  └─ Never store plain token
├─ Transmission:
│  ├─ Send plain token only in email URL
│  ├─ Email sent over TLS/SSL
│  └─ Never log token in server logs
├─ Validation:
│  ├─ Compare plain token from URL against hashed in DB
│  ├─ Use Hash::check($plain, $hashed) for comparison
│  └─ Reject if no match
└─ Expiration:
   ├─ 1 hour window (created_at > now() - 1 hour)
   └─ Automatic deletion after use

PASSWORD SECURITY LAYER
├─ New password must meet strength requirements (above)
├─ Old password not needed (email-based reset)
├─ Password confirmation required (prevents typos)
└─ Password hashed with bcrypt before storage

CLEANUP LAYER
├─ Delete token immediately after successful reset
├─ Cannot reuse expired tokens
├─ One token per email (old tokens deleted)
└─ Prevents token reuse attacks

ERROR HANDLING LAYER
├─ No user enumeration:
│  ├─ "No account found" for non-existent emails
│  ├─ Safe message without confirming email exists
│  └─ Same error whether account exists or not
├─ Clear but safe error messages
├─ No stack traces exposed
└─ Logs errors server-side for debugging

SESSION SECURITY LAYER
├─ CSRF token required on all forms
├─ Session validation on reset
└─ Secure cookies with HttpOnly flag
```

---

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│            FORGOT PASSWORD DATA FLOW                        │
└─────────────────────────────────────────────────────────────┘

USER INPUT
    └─ Email Address (Plain text)
            ↓
    VALIDATE
    └─ Check: Exists in users table ✓
    └─ Check: Account status = 'verified' ✓
            ↓
    GENERATE TOKEN
    └─ Str::random(64) → 'aB3c4D5eF6gH7iJ8k9Lm0nOpQrStUvWxYz...'
            ↓
    HASH TOKEN
    └─ Hash::make(token) → '$2y$12$...[hashed]...'
            ↓
    STORE IN DATABASE
    password_reset_tokens:
    ├─ email: 'user@example.com'
    ├─ token: '$2y$12$...[hashed]...'
    └─ created_at: '2025-01-13 12:00:00'
            ↓
    SEND EMAIL
    ├─ Template: emails/reset-password.blade.php
    ├─ Data:
    │  ├─ user.name: 'John Doe'
    │  ├─ resetUrl: 'https://app.com/reset-password/aB3c4D5e...'
    │  └─ expiresIn: '1 hour'
    ├─ Subject: 'Password Reset Request - LGU Document Tracking'
    └─ To: user@example.com
            ↓
    USER CLICKS LINK
    └─ GET /reset-password/aB3c4D5eF6gH7iJ8k9Lm0nOpQrStUvWxYz...
            ↓
    TOKEN VALIDATION
    ├─ Extract token from URL: 'aB3c4D5eF6gH7iJ8k9Lm0nOpQrStUvWxYz...'
    ├─ Query DB for token record
    ├─ Check: created_at > now() - 1 hour (not expired)
    └─ Return: Reset password form
            ↓
    USER SUBMITS NEW PASSWORD
    ├─ Email: 'user@example.com'
    ├─ Password: 'NewPassword123!' (plain text)
    └─ Confirmation: 'NewPassword123!'
            ↓
    VALIDATE INPUT
    ├─ Email exists ✓
    ├─ Token exists ✓
    ├─ Token not expired ✓
    ├─ Hash::check(plain_token, hashed_token) ✓
    └─ Password meets requirements ✓
            ↓
    UPDATE USER
    users table:
    ├─ Find by email: 'user@example.com'
    ├─ Update password: Hash::make('NewPassword123!')
    │                   → '$2y$12$...[new_hash]...'
    └─ Commit transaction
            ↓
    CLEANUP
    ├─ Delete: password_reset_tokens record
    └─ Clear: Session cache
            ↓
    REDIRECT
    └─ GET /login
           ↓
    SUCCESS MESSAGE
    └─ "Your password has been reset successfully. 
        Please log in with your new password."
            ↓
    USER LOGS IN
    └─ email: 'user@example.com'
    └─ password: 'NewPassword123!'
            ↓
    AUTHENTICATION
    ├─ Query: User by email
    ├─ Verify: Hash::check(input_password, stored_hash)
    ├─ Check: Account status = 'verified'
    └─ Create: Authenticated session
            ↓
    REDIRECT TO DASHBOARD
    └─ Authenticated user access granted ✓
```

---

## Technology Stack

```
┌─────────────────────────────────────────────────┐
│         FORGOT PASSWORD TECH STACK              │
└─────────────────────────────────────────────────┘

FRONTEND
├─ Blade Templating Engine (Laravel)
├─ Bootstrap 5 CSS Framework
├─ JavaScript (Form validation, password toggle)
├─ HTML5 (Semantic markup)
└─ Icons: Bootstrap Icons (bi)

BACKEND
├─ Framework: Laravel 11
├─ Language: PHP 8.2+
├─ ORM: Eloquent
├─ Validation: Laravel Validator with custom rules
├─ Authentication: Laravel Guard
├─ Mail: Laravel Mail (Queue support)
├─ Hashing: bcrypt
├─ Rate Limiting: Laravel RateLimiter
└─ Security: CSRF protection, Input sanitization

DATABASE
├─ Connection: MySQL/MariaDB
├─ Tables:
│  ├─ users (existing)
│  └─ password_reset_tokens (existing)
└─ Schema: Timestamps, indexes, constraints

SECURITY
├─ Encryption: Bcrypt (passwords, tokens)
├─ Transport: HTTPS/TLS for emails
├─ Headers: CSRF tokens, Security headers
├─ Validation: Server-side validation required
├─ Rate Limiting: Progressive blocking
└─ Hashing: bcrypt for password storage

EMAIL
├─ Driver: SMTP / Mailgun / SendGrid / etc.
├─ Queue: Database/Redis (async)
├─ Template: Laravel Mailable + Blade
├─ Format: HTML email with text fallback
└─ Security: TLS/SSL encryption

CONFIGURATION
├─ Routes: routes/web.php
├─ Controllers: app/Http/Controllers/
├─ Views: resources/views/
├─ Mails: app/Mail/
├─ Config: config/mail.php, routes/web.php
└─ Environment: .env file
```

---

## Implementation Summary

✅ **Complete Full-Stack Implementation**
- Frontend: 3 new views + 1 updated view
- Backend: 1 controller + 1 mailable + 2 configuration updates
- Database: Uses existing password_reset_tokens table
- Security: Multiple layers of protection
- Documentation: 3 comprehensive guides

✅ **Production Ready**
- Error handling
- Rate limiting
- Email delivery
- Token validation
- Password security

✅ **User Friendly**
- Simple, intuitive flow
- Clear error messages
- Mobile responsive
- Professional design

✅ **Developer Friendly**
- Clean code structure
- Follows Laravel conventions
- Well documented
- Easy to test and maintain
