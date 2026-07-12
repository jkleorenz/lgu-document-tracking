# AGENTS.md — System Context for AI Agents

> Read this file at the start of every new session to understand the LGU Document Tracking system.

---

## Project Overview

**What:** A document tracking system for Local Government Units (LGUs) in the Philippines. Tracks documents as they move between departments via QR code scanning.

**Tech Stack:**
- Backend: Laravel 10, PHP 8.1+, MySQL
- Frontend: Blade templates, Bootstrap 5, Vite, SweetAlert2
- Auth: Spatie Laravel Permission (roles/permissions)
- PDF: barryvdh/laravel-dompdf
- DOCX: phpoffice/phpword
- QR: simplesoftwareio/simple-qrcode
- Scanning: @zxing/library (browser-based QR scanning)

**Timezone:** `Asia/Manila`

---

## Directory Structure

```
app/
├── Http/Controllers/    # 9 controllers
├── Http/Middleware/      # SecurityHeaders, SecurityMonitoring, HoneypotProtection
├── Models/              # 8 models (User, Document, DocumentStatusLog, Department, Notification, AuditLog, LoginAttempt, Otp)
├── Services/            # NotificationService, QRCodeService
├── Providers/           # AppServiceProvider (gates), RouteServiceProvider (rate limiters)
database/
├── migrations/          # 16 migrations
├── seeders/             # RoleAndPermissionSeeder, DepartmentSeeder, UserSeeder
resources/views/         # Blade templates (see Views section)
routes/web.php           # All routes (no API beyond /api/user)
```

---

## Database Schema (Key Tables)

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `users` | All users | name, email, password, department_id (FK), status (pending/verified/rejected), profile_picture, failed_login_attempts, locked_until, last_login_at, last_login_ip. SoftDeletes. |
| `departments` | 23 LGU departments | name, code (unique), head_id (FK->users), is_active |
| `documents` | Tracked documents | document_number (unique, DOC-YYYYMM-XXXX), title, description, document_type, qr_code_path, created_by (FK->users), department_id (FK->departments), status (enum), is_priority, archived_at. SoftDeletes. |
| `document_status_logs` | Audit trail | document_id, updated_by (FK->users), old_status, new_status, remarks, action_date |
| `notifications` | User notifications | user_id, document_id (nullable), title, message, type (info/success/warning/danger), is_read |
| `audit_logs` | Security audit | user_id, action, model_type, model_id, old_values (json), new_values (json), ip_address, user_agent |
| `login_attempts` | Brute force protection | email, ip_address, success, user_agent |
| `otps` | OTP codes | email, code, attempts, expires_at |
| `model_has_roles` | Spatie pivot | Links users to roles |

---

## Models & Relationships

```
User
├── belongsTo Department
├── hasMany Document (as creator, via created_by)
├── hasMany Notification
├── hasMany DocumentStatusLog (as updater, via updated_by)
└── HasRoles (Spatie)

Document
├── belongsTo User (as creator, via created_by)
├── belongsTo Department
├── hasMany DocumentStatusLog
├── hasMany Notification
└── hasOneThrough User (currentHandler — last person who touched it)

Department
├── belongsTo User (head)
├── hasMany User
└── hasMany Document

DocumentStatusLog
├── belongsTo Document
└── belongsTo User (updatedBy)

Notification
├── belongsTo User
└── belongsTo Document (nullable)
```

---

## Controllers & Authorization

### Authorization Pattern (Critical)
```php
// Used in DocumentController and ScanController
protected function authorizeDocumentAccess(Document $document): void
{
    $user = Auth::user();
    if ($user->hasRole('Administrator') || $user->hasRole('Mayor')) return;  // Full access
    if ($user->department_id && $user->department_id === $document->department_id) return;  // Dept match
    abort(403);
}
```

### Gates (AppServiceProvider)
| Gate | Allowed Roles |
|------|---------------|
| `verify-users` | Administrator |
| `manage-documents` | Administrator, Mayor, LGU Staff, Department Head |
| `archive-documents` | Administrator, Mayor, LGU Staff, Department Head |
| `set-priority` | Administrator, Mayor |
| `reset user passwords` | Administrator |

### Controller Summary
| Controller | Auth Pattern | Key Methods |
|-----------|-------------|-------------|
| `AuthController` | Guest (login/register), Auth (logout) | login (rate limited), register, logout |
| `DocumentController` | `authorizeDocumentAccess()` + Gates | CRUD, updateStatus, setPriority, archive, approve, reject, printQRCode, serveQrCode, generateReport (PDF/DOCX) |
| `ScanController` | `authorizeDocumentAccess()` + role check | index, scan, quickUpdate, complete, returnDocument |
| `UserController` | Gate `verify-users` | CRUD, verify, reject, resetPassword |
| `DashboardController` | Role check (Admin->admin dashboard, Staff->staff dashboard) | index |
| `NotificationController` | Ownership check (user_id == Auth::id()) | index, markAsRead, markAllAsRead, destroy, unreadCount, recent |
| `ArchiveController` | Ownership/dept check | index, show, restore, destroy |
| `ProfileController` | Auth (own profile only) | show, edit, update, updatePassword, updateProfilePicture |

---

## Routes Summary

**Public:** `/` redirects to login

**Guest (throttled):**
- `GET/POST /login` — `throttle:login` (5/15min)
- `GET/POST /register` — `throttle:register` (3/hr)

**Protected:**
- `GET /dashboard` — role-based dashboard
- `resources /documents` — full CRUD
- `POST /documents/{id}/priority|archive|approve|update-status|reject`
- `GET /documents/{id}/print-qr|qr-code|timeline|report`
- `GET /scan` — QR scanner page
- `POST /scan` — process scan (`throttle:scan`, 30/min)
- `POST /scan/quick-update|complete|return`
- `GET /notifications` + AJAX endpoints
- `GET /archive` + `POST /archive/{id}/restore` + `DELETE /archive/{id}`
- `resources /users` — admin only (`role:Administrator` middleware)
- `GET /users-pending` + `POST /users/{id}/verify|reject`
- `GET/POST /users/{id}/password/reset`
- Profile routes (`/profile`, `/settings`)

---

## Services

### NotificationService (`app/Services/NotificationService.php`)
Sends notifications for 6 document lifecycle events. Each event notifies:
1. **Receiving department users** (via `notifyDepartmentUsers()` — dept_id match)
2. **Creator** (via `notifyCreator()` — always, unless they're the actor)
3. **Administrators/Mayors** (via `notifyAdministrators()` — role check, excludes duplicates)

**Event methods:**
- `onDocumentForwarded()` — receiving dept + admins
- `onDocumentReceivedViaQRScan()` — creator + admins
- `onDocumentScannedViaQR()` — creator + admins
- `onDocumentReturned()` — target dept + creator + admins
- `onDocumentCompleted()` — creator + admins
- `onDocumentArchivedNotCompleted()` — creator + admins
- `onDocumentRetrieved()` — creator + admins

**Duplicate prevention:** `Notification::createNotification()` checks for same title+doc within 30 seconds.

### QRCodeService (`app/Services/QRCodeService.php`)
- Generates SVG QR codes pointing to `/scan?document={DOC-NUMBER}`
- Stores in `storage/app/qrcodes/` (NOT public directory)
- Served via `DocumentController::serveQrCode()` with auth check

---

## Roles & Permissions

| Permission | Administrator | Mayor | LGU Staff | Department Head |
|------------|:---:|:---:|:---:|:---:|
| view documents | Y | Y | Y | Y |
| view all documents | Y | Y | - | - |
| create documents | Y | Y | Y | Y |
| edit documents | Y | Y | - | - |
| delete documents | Y | - | - | - |
| archive documents | Y | Y | Y | Y |
| set priority | Y | Y | - | - |
| scan qr codes | Y | Y | Y | Y |
| update status | Y | Y | Y | Y |
| manage/verify users | Y | - | - | - |
| receive notifications | Y | Y | Y | Y |
| reset user passwords | Y | - | - | - |

**Note:** LGU Staff and Department Head have **identical privileges**.

---

## Document Status Lifecycle

```
Created (Forwarded) ──> Received ──> Under Review ──> Completed (auto-archived)
                    │                │
                    ├──> Return ──> (re-scan) ──> Received ...
                    ├──> Forwarded (to another dept) ...
                    ├──> Approved (auto-archived)
                    └──> Rejected

Archived (manual) ──> Retrieved ──> (scan) ──> Received ...
Completed (auto-archived) ──> Retrieved ...
```

**Valid statuses:** Pending, Received, Under Review, Forwarded, Approved, Rejected, Return, Retrieved, Completed, Archived

---

## Security Features

1. **Account Lockout:** 5 failed attempts → 30-minute lock
2. **IP Blocking:** 10 failures from same IP in 15 min → blocked
3. **Rate Limiting:** login (5/15min), register (3/hr), scan (30/min)
4. **CSRF:** Custom VerifyCsrfToken with logging + JSON error handling
5. **Security Headers:** CSP, X-Frame-Options: DENY, HSTS (prod), X-XSS-Protection
6. **Security Monitoring:** Detects XSS, SQLi, path traversal, command injection in real-time
7. **Honeypot Protection:** Hidden field bot detection
8. **Session:** Encrypted, SameSite: strict, HTTP-only
9. **Password Policy:** 12+ chars, mixed case, numbers, symbols, checked against breached databases
10. **Audit Logging:** All significant actions logged with IP, user-agent, old/new values
11. **Document Number Immutability:** Boot event prevents changes after creation
12. **Pessimistic Locking:** Document number generation uses DB transaction locks
13. **Open Redirect Prevention:** Whitelist-based intended URL validation

---

## Key Conventions

### Naming
- Document numbers: `DOC-YYYYMM-XXXX` (e.g., `DOC-202607-0001`)
- Department codes: Uppercase short codes (e.g., `MAYOR`, `BUDGET`, `MEO`)
- QR code files: `qrcode_{DOC-NUMBER}.svg` in `storage/app/qrcodes/`

### Patterns to Follow
- **Authorization:** Use `authorizeDocumentAccess()` in DocumentController/ScanController for document-level access
- **Notifications:** Use `NotificationService` event methods, never create notifications directly
- **Audit Logging:** Use `AuditLog::log()` for all significant actions
- **Validation:** Passwords always `Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()`
- **Roles:** Check with `$user->hasRole('Administrator')` or `$user->hasAnyRole([...])`
- **Error Messages:** Never expose `$e->getMessage()` to users; log it, show generic message
- **QR Codes:** Always served via `documents.qr-code` route (auth required), never direct file access

### File Locations
- QR codes: `storage/app/qrcodes/` (served via controller, NOT public)
- Profile pictures: `storage/app/public/profile-pictures/` (via `Storage::disk('public')`)
- Compiled assets: `public/build/` (via Vite)
- Logs: `storage/logs/laravel.log`

---

## Environment Setup

**Local Development:**
- `.env`: `APP_ENV=local`, `APP_DEBUG=false`, `CACHE_DRIVER=file`, `SESSION_DRIVER=file`
- DB: MySQL on localhost:3306, database `lgu_document_tracking`, user `root`, no password
- Build: `npm run build` (Vite) or `npm run dev` (hot reload)
- Serve: `php artisan serve`

**Key Commands:**
```bash
php artisan migrate              # Run migrations
php artisan db:seed              # Seed database
php artisan route:clear          # Clear route cache
php artisan config:clear         # Clear config cache
php artisan view:clear           # Clear compiled views
php artisan cache:clear          # Clear application cache
npm run build                    # Compile frontend assets
```

---

## TODOs / Known Issues

- CSP `unsafe-inline` for `script-src` and `style-src` — 16 Blade templates have inline `<script>` blocks; needs refactor to external JS files
- `password_reset_tokens` table exists but no routes use it (dead config)
- `public/qrcodes/` directory still contains old QR code files from before security fix (should be migrated to `storage/app/qrcodes/`)
- LGU Staff and Department Head have identical permissions — consider merging or differentiating
- No self-service password reset — admin-only (by design choice)
