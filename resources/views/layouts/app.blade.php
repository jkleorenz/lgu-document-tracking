<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LGU Document Tracking System')</title>
    
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    
    <style>
        /* Modern Design System */
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #3b82f6;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #0ea5e9;
            --dark: #0f172a;
            --light-bg: #f8fafc;
            --sidebar-width: 260px;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f1f5f9;
            background-attachment: fixed;
            min-height: 100vh;
        }
        
        /* Sidebar Enhancement */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            margin-bottom: 8px;
        }
        
        .sidebar-brand h5 {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.25rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .sidebar-brand small {
            color: var(--secondary-color);
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .sidebar .nav-link {
            color: #475569;
            padding: 12px 20px;
            margin: 4px 12px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(37, 99, 235, 0.08);
            color: var(--primary-color);
            transform: translateX(4px);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        
        .sidebar .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: white;
            border-radius: 0 4px 4px 0;
        }
        
        .sidebar .nav-link i {
            margin-right: 12px;
            width: 20px;
            font-size: 1.1rem;
        }
        
        .sidebar-section-title {
            padding: 24px 20px 8px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--secondary-color);
        }
        
        /* Main Content Area */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        .top-navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            padding: 16px 32px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.04);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .main-content {
            padding: 32px;
        }
        
        /* Enhanced Cards */
        .card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            margin-bottom: 24px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card:hover {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        
        .card-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(249, 250, 251, 0.9) 100%);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            font-weight: 600;
            padding: 20px 24px;
            font-size: 1.1rem;
            color: var(--dark);
        }
        
        .card-body {
            padding: 24px;
        }
        
        /* Stat Cards with Gradients */
        .stat-card {
            position: relative;
            overflow: hidden;
            border: none;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        
        /* Clickable Stat Cards */
        .clickable-card {
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .clickable-card:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.18);
        }
        
        .clickable-card small {
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .clickable-card:hover small {
            opacity: 1;
        }
        
        /* Quick Action Buttons */
        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 24px 16px;
            background: white;
            border: 2px solid rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 140px;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border-color: currentColor;
        }
        
        .quick-action-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            transition: all 0.3s;
        }
        
        .quick-action-icon i {
            font-size: 28px;
        }
        
        .quick-action-btn:hover .quick-action-icon {
            transform: scale(1.1);
        }
        
        .quick-action-btn span {
            font-weight: 600;
            font-size: 0.95rem;
            line-height: 1.4;
        }
        
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        /* Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
        }
        
        /* Make Pending badge bright yellow */
        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #000 !important;
            font-weight: 600;
        }
        
        .badge-priority {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.85; transform: scale(1.05); }
        }
        
        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn i {
            font-size: 1rem;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        
        .btn-success {
            background: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .btn-danger {
            background: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .btn-warning {
            background: var(--warning-color);
            color: white;
        }
        
        .btn-warning:hover {
            background: #d97706;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        
        .btn-info {
            background: var(--info-color);
            color: white;
        }
        
        .btn-info:hover {
            background: #0284c7;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }
        
        .btn-secondary {
            background: var(--secondary-color);
            color: white;
        }
        
        .btn-secondary:hover {
            background: #475569;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.3);
        }
        
        .btn-sm {
            padding: 6px 14px;
            font-size: 0.85rem;
        }
        
        /* Form Controls */
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 10px 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        
        /* Table Enhancement */
        .table {
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table thead th {
            background: rgba(248, 250, 252, 0.8);
            border: none;
            padding: 16px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--secondary-color);
        }
        
        .table tbody tr {
            transition: all 0.2s;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .table tbody tr:hover {
            background: rgba(37, 99, 235, 0.04);
            transform: scale(1.01);
        }
        
        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
        }
        
        /* User Dropdown */
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            border-radius: 12px;
            background: rgba(248, 250, 252, 0.8);
            transition: all 0.3s;
        }
        
        .user-dropdown:hover {
            background: rgba(226, 232, 240, 0.8);
        }
        
        /* Progress Bar */
        .progress {
            height: 10px;
            border-radius: 10px;
            background: #e2e8f0;
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            transition: width 0.6s ease;
        }
        
        /* Alert Messages */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 16px 20px;
            animation: slideDown 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-wrapper {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    @auth
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <h5><i class="bi bi-file-text"></i> LGU DocTrack</h5>
            <small>Document Management System</small>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link position-relative {{ request()->routeIs('documents.*') ? 'active' : '' }}" href="{{ route('documents.index') }}">
                    <i class="bi bi-file-earmark-text"></i> Documents
                    @if(auth()->user()->pendingDocumentsCount() > 0)
                    <span class="notification-badge">{{ auth()->user()->pendingDocumentsCount() }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('scan.*') ? 'active' : '' }}" href="{{ route('scan.index') }}">
                    <i class="bi bi-qr-code-scan"></i> Scan QR Code
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link position-relative {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                    <i class="bi bi-bell"></i> Notifications
                    @if(auth()->user()->unreadNotificationsCount() > 0)
                    <span class="notification-badge">{{ auth()->user()->unreadNotificationsCount() }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('archive.*') ? 'active' : '' }}" href="{{ route('archive.index') }}">
                    <i class="bi bi-archive"></i> Archive
                </a>
            </li>
            
            @role('Administrator')
            <div class="sidebar-section-title">ADMINISTRATION</div>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <i class="bi bi-people"></i> User Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link position-relative {{ request()->routeIs('users.pending') ? 'active' : '' }}" href="{{ route('users.pending') }}">
                    <i class="bi bi-person-check"></i> Pending Verifications
                    @if(App\Models\User::where('status', 'pending')->count() > 0)
                    <span class="badge bg-warning text-dark ms-auto">{{ App\Models\User::where('status', 'pending')->count() }}</span>
                    @endif
                </a>
            </li>
            @endrole
        </ul>
    </nav>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Navigation Bar -->
        <nav class="top-navbar">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted"><i class="bi bi-calendar3"></i> {{ date('F d, Y') }}</span>
                </div>
                <div class="dropdown">
                    <a class="user-dropdown dropdown-toggle text-decoration-none" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if(auth()->user()->profile_picture)
                            <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" 
                                 alt="Profile" 
                                 class="rounded-circle" 
                                 style="width: 40px; height: 40px; object-fit: cover; margin-right: 8px;">
                        @else
                            <i class="bi bi-person-circle" style="font-size: 1.5rem;"></i>
                        @endif
                        <div>
                            <div style="font-weight: 600; font-size: 0.9rem;">{{ auth()->user()->name }}</div>
                            <small class="text-muted">{{ auth()->user()->roles->first()->name }}</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="bi bi-person"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('settings') }}"><i class="bi bi-gear"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Alerts -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @yield('content')
        </div>
    </div>
    @else
    @yield('content')
    @endauth

    <!-- Bootstrap Icons & Google Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</body>
</html>

