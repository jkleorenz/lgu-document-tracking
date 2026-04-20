@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<style>
    /* Unread Notification Styling */
    .notification-item.unread {
        background: linear-gradient(90deg, rgba(13, 110, 253, 0.08) 0%, rgba(13, 110, 253, 0.02) 100%);
        border-left: 4px solid #0d6efd;
    }
    
    .notification-item {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }
    
    .notification-item:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-bell"></i> Notifications</h2>
        @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-check-all"></i> Mark All as Read
            </button>
        </form>
        @endif
    </div>

    @if($unreadCount > 0)
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> You have <strong>{{ $unreadCount }}</strong> unread notification{{ $unreadCount > 1 ? 's' : '' }}
    </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            @forelse($notifications as $notification)
            <div class="notification-item d-flex align-items-start p-3 border-bottom {{ !$notification->is_read ? 'unread' : '' }}" data-notification-id="{{ $notification->id }}" style="cursor: pointer;" onclick="handleNotificationClick(event, {{ $notification->id }}, {{ !$notification->is_read ? 'true' : 'false' }})">
                <div class="me-3">
                    @if(in_array($notification->title, ['Document Scanned via QR Code', 'Document Received via QR Scan']))
                        <span aria-hidden="true" style="width: 24px; height: 24px; display: inline-block; background-color: var(--bs-primary); -webkit-mask: url('{{ asset('images/scanner.png') }}') no-repeat center / contain; mask: url('{{ asset('images/scanner.png') }}') no-repeat center / contain;"></span>
                    @elseif($notification->title === 'Document Retrieved from Archive')
                        <i class="bi bi-question-circle-fill text-warning" style="font-size: 1.5rem;"></i>
                    @elseif(\Illuminate\Support\Str::startsWith($notification->title, 'Document Returned'))
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 1.5rem;"></i>
                    @elseif($notification->type == 'success')
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>
                    @elseif($notification->type == 'warning')
                        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 1.5rem;"></i>
                    @elseif($notification->type == 'danger')
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 1.5rem;"></i>
                    @else
                        <i class="bi bi-info-circle-fill text-info" style="font-size: 1.5rem;"></i>
                    @endif
                </div>
                
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 fw-bold">
                                {{ $notification->title }}
                                @if(!$notification->is_read)
                                <span class="badge bg-primary ms-2">New</span>
                                @endif
                            </h6>
                            <p class="mb-2 text-secondary">{{ $notification->message }}</p>
                            @if($notification->document)
                            <a href="{{ route('documents.show', $notification->document) }}" class="text-decoration-none small text-primary">
                                <i class="bi bi-arrow-right-circle"></i> View Document: <strong>{{ $notification->document->document_number }}</strong>
                            </a>
                            @endif
                        </div>
                        <div class="text-end" style="min-width: 150px;">
                            <small class="text-muted d-block mb-2">{{ $notification->created_at->diffForHumans() }}</small>
                            <div class="d-flex gap-1 justify-content-end">
                                @if(!$notification->is_read)
                                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary" title="Mark as read">
                                        <i class="bi bi-check"></i>
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('notifications.destroy', $notification) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this notification?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="bi bi-bell-slash" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No notifications yet</p>
            </div>
            @endforelse

            <div class="mt-3 px-3 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    Showing {{ $notifications->firstItem() ?? 0 }} to {{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }} results
                </div>
                <div class="pagination-wrap">
                    <nav aria-label="Notification pagination">
                        <ul class="pagination mb-0">
                            {{-- Previous Page Link --}}
                            @if ($notifications->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">Previous</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $notifications->previousPageUrl() }}" rel="prev">Previous</a>
                                </li>
                            @endif

                            {{-- Custom Pagination Elements with 1-7 pages limit --}}
                            @php
                                $currentPage = $notifications->currentPage();
                                $lastPage = $notifications->lastPage();
                                $maxPages = 7;
                                
                                // Generate custom pagination elements
                                $customElements = [];
                                
                                if ($lastPage <= $maxPages) {
                                    // Show all pages if total pages is less than or equal to max
                                    for ($i = 1; $i <= $lastPage; $i++) {
                                        $customElements[$i] = $notifications->url($i);
                                    }
                                } else {
                                    // Show limited pages with ellipsis
                                    if ($currentPage <= 4) {
                                        // Current page is in the first 4 pages
                                        for ($i = 1; $i <= 5; $i++) {
                                            $customElements[$i] = $notifications->url($i);
                                        }
                                        $customElements['...'] = 'ellipsis';
                                        $customElements[$lastPage] = $notifications->url($lastPage);
                                    } elseif ($currentPage >= $lastPage - 3) {
                                        // Current page is in the last 4 pages
                                        $customElements[1] = $notifications->url(1);
                                        $customElements['...'] = 'ellipsis';
                                        for ($i = $lastPage - 4; $i <= $lastPage; $i++) {
                                            $customElements[$i] = $notifications->url($i);
                                        }
                                    } else {
                                        // Current page is in the middle
                                        $customElements[1] = $notifications->url(1);
                                        $customElements['...'] = 'ellipsis';
                                        for ($i = $currentPage - 1; $i <= $currentPage + 1; $i++) {
                                            $customElements[$i] = $notifications->url($i);
                                        }
                                        $customElements['...'] = 'ellipsis';
                                        $customElements[$lastPage] = $notifications->url($lastPage);
                                    }
                                }
                            @endphp
                            
                            @foreach ($customElements as $page => $url)
                                @if ($page === '...')
                                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">...</span></li>
                                @elseif ($page == $currentPage)
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($notifications->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $notifications->nextPageUrl() }}" rel="next">Next</a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">Next</span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-mark all unread notifications as read after a short delay when the page loads
document.addEventListener('DOMContentLoaded', function() {
    const autoReadDelayMs = 1500; // wait 1.5 seconds so users can scan the list
    const staggerIntervalMs = 150; // spread requests to keep the UI responsive

    const unreadNotifications = document.querySelectorAll('.notification-item.unread');

    if (!unreadNotifications.length) {
        return;
    }

    setTimeout(() => {
        unreadNotifications.forEach(function(notificationElement, index) {
            const notificationId = notificationElement.getAttribute('data-notification-id');
            if (!notificationId) {
                return;
            }

            setTimeout(() => {
                markNotificationAsRead(notificationId, notificationElement);
            }, index * staggerIntervalMs);
        });
    }, autoReadDelayMs);
});

// Function to mark a notification as read via AJAX
function markNotificationAsRead(notificationId, notificationElement = null) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                      document.querySelector('input[name="_token"]')?.value;
    
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    })
    .then(response => {
        if (response.ok) {
            // Update UI if notificationElement is provided
            if (notificationElement) {
                notificationElement.classList.remove('unread');
                // Remove the "New" badge if present
                const newBadge = notificationElement.querySelector('.badge.bg-primary');
                if (newBadge) {
                    newBadge.remove();
                }
                // Remove the mark as read button if present
                const markReadBtn = notificationElement.querySelector('button[type="submit"][class*="btn-primary"]');
                if (markReadBtn) {
                    markReadBtn.closest('form')?.remove();
                }
            }
            // Refresh unread count
            updateUnreadCount();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

// Function to update unread count
function updateUnreadCount() {
    fetch('{{ route("notifications.unread-count") }}')
        .then(response => response.json())
        .then(data => {
            const unreadCountElement = document.querySelector('.unread-count-badge');
            if (unreadCountElement) {
                if (data.count > 0) {
                    unreadCountElement.textContent = data.count;
                    unreadCountElement.style.display = 'inline-block';
                } else {
                    unreadCountElement.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Error updating unread count:', error));
}

// Auto-refresh notification count every 10 seconds (more frequent)
// This works in conjunction with the global script in app.blade.php
setInterval(function() {
    fetch('{{ route("notifications.unread-count") }}')
        .then(response => response.json())
        .then(data => {
            // Update badge in sidebar (use ID selector for more reliable targeting)
            const badge = document.getElementById('notification-badge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline';
                } else {
                    badge.textContent = '0';
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Error fetching notification count:', error));
}, 10000);

// Handle notification click to mark as read
function handleNotificationClick(event, notificationId, isUnread) {
    // Don't trigger if clicking on a button or link
    if (event.target.closest('button') || event.target.closest('a') || event.target.closest('form')) {
        return;
    }
    
    // Only mark as read if it's currently unread
    if (isUnread) {
        const notificationElement = event.currentTarget;
        markNotificationAsRead(notificationId, notificationElement);
    }
}
</script>
@endsection
