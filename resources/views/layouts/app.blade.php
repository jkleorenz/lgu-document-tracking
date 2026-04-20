<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LGU Document Tracking System')</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="{{ Request::is('login') || Request::is('register') || Request::is('password/*') ? 'login-page' : '' }}">
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
                    <i class="bi bi-speedometer2"></i> <span class="nav-link-label">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link position-relative {{ request()->routeIs('documents.*') ? 'active' : '' }}" href="{{ route('documents.index') }}">
                    <i class="bi bi-file-earmark-text"></i> <span class="nav-link-label">Documents</span>
                    @if(auth()->user()->pendingDocumentsCount() > 0)
                    <span class="notification-badge">{{ auth()->user()->pendingDocumentsCount() }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('scan.*') ? 'active' : '' }}" href="{{ route('scan.index') }}">
                    <i class="bi bi-qr-code-scan"></i> <span class="nav-link-label">Scan QR Code</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link position-relative {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                    <i class="bi bi-bell"></i> <span class="nav-link-label">Notifications</span>
                    @php
                        try {
                            $unreadCount = auth()->user() ? auth()->user()->unreadNotificationsCount() : 0;
                        } catch (\Exception $e) {
                            $unreadCount = 0;
                        }
                    @endphp
                    <span class="notification-badge" id="notification-badge" style="display: {{ $unreadCount > 0 ? 'inline' : 'none' }};">{{ $unreadCount }}</span>
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
                    <i class="bi bi-people"></i> <span class="nav-link-label">User Management</span>
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
                                 style="width: 40px; height: 40px; object-fit: cover; margin-right: 8px;"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                            <i class="bi bi-person-circle" style="font-size: 1.5rem; display: none;"></i>
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
            @php
                $swalFlashData = [
                    'success' => session('success'),
                    'error' => session('error') ?: session('message'),
                    'validationErrors' => $errors->any() ? $errors->all() : [],
                ];
            @endphp
            <script id="swal-flash-data" type="application/json">
                @json($swalFlashData)
            </script>

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
    
    <!-- Scripts Stack -->
    @stack('scripts')
    
    <!-- Global Scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle clickable rows
        document.body.addEventListener('click', function(e) {
            const clickableRow = e.target.closest('.clickable-row');
            if (clickableRow) {
                // Don't trigger if clicking a button, link, or form element
                if (e.target.closest('button') || 
                    e.target.closest('a') || 
                    e.target.closest('form') || 
                    e.target.closest('.btn') ||
                    e.target.closest('.dropdown') ||
                    e.target.tagName === 'INPUT' ||
                    e.target.tagName === 'SELECT' ||
                    e.target.tagName === 'TEXTAREA' ||
                    e.target.isContentEditable) {
                    return;
                }
                
                const href = clickableRow.dataset.href;
                if (href) {
                    window.location.href = href;
                }
            }
        });
    });
    </script>
    
    <!-- Global Notification Badge Update Script -->
    <script>
    // Update notification badge on page load and periodically
    (function() {
        function updateNotificationBadge() {
            // Only run if user is authenticated (check if badge element exists)
            const badge = document.getElementById('notification-badge');
            if (!badge) {
                return; // User not authenticated, skip
            }
            
            fetch('{{ route("notifications.unread-count") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    // Check if response is OK
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (badge && data && typeof data.count !== 'undefined') {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.style.display = 'inline';
                        } else {
                            badge.textContent = '0';
                            badge.style.display = 'none';
                        }
                    }
                })
                .catch(error => {
                    // Silently fail - don't show errors to user
                    // Just hide the badge if there's an error
                    if (badge) {
                        badge.style.display = 'none';
                    }
                    console.error('Error fetching notification count:', error);
                });
        }
        
        // Only update if user is authenticated (badge element exists)
        // Update immediately on page load (with small delay to ensure DOM is ready)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(updateNotificationBadge, 100);
            });
        } else {
            setTimeout(updateNotificationBadge, 100);
        }
        
        // Update every 60 seconds (only if badge exists)
        setInterval(function() {
            if (document.getElementById('notification-badge') && document.visibilityState === 'visible') {
                updateNotificationBadge();
            }
        }, 60000);
    })();
    </script>
</body>
</html>

