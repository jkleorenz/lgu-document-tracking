# Digital Document Tracking System for LGUs

A comprehensive web-based platform designed to track and manage physical documents within Local Government Units (LGUs).

## Features

- **Role-Based Access Control**: Administrator, LGU Staff, and Department Head roles
- **Document Management**: Create, track, and manage documents with unique identifiers
- **QR Code Integration**: Generate and scan QR codes for document tracking
- **Real-Time Notifications**: In-system notifications for document status updates
- **Document Tracking**: Complete audit trail of document movement and status changes
- **Priority Management**: Mark urgent documents as priority
- **Archive System**: Archive completed documents while maintaining searchability
- **Secure Authentication**: Role-based access with account verification

## Technologies Used

- **Backend**: Laravel 10+ (PHP 8.1+)
- **Frontend**: HTML, CSS, JavaScript, Bootstrap 5
- **Database**: MySQL
- **QR Code**: SimpleSoftware QR Code
- **Permissions**: Spatie Laravel Permission

## System Requirements

- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL >= 5.7
- Apache/Nginx Web Server

## Installation

1. **Clone or extract the project**
   ```bash
   cd lgu-document-tracking
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database**
   - Create a MySQL database named `lgu_document_tracking`
   - Update `.env` file with your database credentials:
     ```
     DB_DATABASE=lgu_document_tracking
     DB_USERNAME=root
     DB_PASSWORD=your_password
     ```

6. **Run Migrations and Seeders**
   ```bash
   php artisan migrate --seed
   ```

7. **Create Storage Link**
   ```bash
   php artisan storage:link
   ```

8. **Build Frontend Assets**
   ```bash
   npm run build
   ```

9. **Start Development Server**
   ```bash
   php artisan serve
   ```

10. **Access the Application**
    - URL: `http://localhost:8000`
    - Default Admin Credentials:
      - Email: `admin@lgu.gov`
      - Password: `password`

## User Roles

### Administrator
- Verify and manage user accounts
- Generate QR codes for documents
- Set document priority levels
- Manage departments and users
- View comprehensive logs and reports
- Full system access

### LGU Staff
- Encode new documents
- Scan QR codes to update status
- View their created/processed documents
- Receive notifications
- Track document progress

### Department Head
- View forwarded documents
- Update document status
- Archive completed documents
- Scan QR codes
- Receive notifications
- Manage department documents

## Project Structure

```
lgu-document-tracking/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   └── Services/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── css/
│   ├── js/
│   └── qrcodes/
├── resources/
│   ├── js/
│   ├── scss/
│   └── views/
└── routes/
```

## Key Features Documentation

### Document Management
- Create documents with title, description, and department assignment
- Automatic unique identifier generation
- QR code generation for each document
- Status tracking (Pending, Under Review, Approved, etc.)
- Priority flagging for urgent documents

### QR Code System
- Automatic generation upon document creation
- Unique QR code for each document
- Printable format for physical attachment
- Mobile-friendly scanning interface
- Instant document retrieval via QR scan

### Notification System
- Real-time in-system notifications
- Notification triggers:
  - New document creation
  - Status updates
  - Priority changes
  - Document forwarding
  - Archive actions

### Security Features
- Bcrypt password hashing
- CSRF protection
- Role-based middleware
- Input validation and sanitization
- Session timeout management
- Secure authentication flow

## Database Schema

### Main Tables
- `users` - User accounts and profiles
- `roles` - User role definitions
- `permissions` - Permission definitions
- `documents` - Document records
- `document_status_logs` - Document tracking history
- `notifications` - User notifications
- `departments` - Department management

## API Routes

```
POST   /login              - User login
POST   /register           - User registration
POST   /logout             - User logout
GET    /dashboard          - Role-based dashboard
GET    /documents          - List documents
POST   /documents          - Create document
GET    /documents/{id}     - View document
PUT    /documents/{id}     - Update document
GET    /scan               - QR code scanner
POST   /scan               - Process QR scan
GET    /notifications      - View notifications
GET    /archive            - View archived documents
```

## Development

### Running Development Server
```bash
php artisan serve
npm run dev
```

### Running Tests
```bash
php artisan test
```

### Clearing Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Troubleshooting

### Permission Issues
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### QR Code Directory
Ensure `public/qrcodes` directory exists and is writable:
```bash
mkdir -p public/qrcodes
chmod -R 775 public/qrcodes
```

## Support

For issues and questions, please refer to the project documentation or contact the development team.

## License

This project is open-source software licensed under the MIT license.

