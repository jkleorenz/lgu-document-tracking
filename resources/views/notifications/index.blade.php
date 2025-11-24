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
    
    /* Custom Pagination - No Arrows, Text Only */
    .pagination {
        font-size: 0.875rem;
        gap: 4px;
    }
    
    .pagination .page-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        min-width: 40px;
        text-align: center;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        color: #495057;
        transition: all 0.2s ease;
        font-weight: 500;
    }
    
    .pagination .page-link:hover {
        background-color: #e9ecef;
        border-color: #adb5bd;
        color: #212529;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
        font-weight: 600;
    }
    
    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Previous/Next text styling */
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        padding: 0.5rem 1rem;
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
            <div class="notification-item d-flex align-items-start p-3 border-bottom {{ !$notification->is_read ? 'unread' : '' }}">
                <div class="me-3">
                    @if($notification->type == 'success')
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
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            Showing {{ $notifications->firstItem() ?? 0 }} to {{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }} results
        </div>
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

                {{-- Page Numbers --}}
                @foreach ($notifications->getUrlRange(1, $notifications->lastPage()) as $page => $url)
                    @if ($page == $notifications->currentPage())
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

<script>
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
</script>
@endsection

