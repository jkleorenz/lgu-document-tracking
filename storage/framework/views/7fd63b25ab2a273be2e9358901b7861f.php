

<?php $__env->startSection('title', 'Notifications'); ?>

<?php $__env->startSection('content'); ?>
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
        <?php if($unreadCount > 0): ?>
        <form method="POST" action="<?php echo e(route('notifications.read-all')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-check-all"></i> Mark All as Read
            </button>
        </form>
        <?php endif; ?>
    </div>

    <?php if($unreadCount > 0): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> You have <strong><?php echo e($unreadCount); ?></strong> unread notification<?php echo e($unreadCount > 1 ? 's' : ''); ?>

    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="notification-item d-flex align-items-start p-3 border-bottom <?php echo e(!$notification->is_read ? 'unread' : ''); ?>">
                <div class="me-3">
                    <?php if($notification->type == 'success'): ?>
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>
                    <?php elseif($notification->type == 'warning'): ?>
                    <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 1.5rem;"></i>
                    <?php elseif($notification->type == 'danger'): ?>
                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 1.5rem;"></i>
                    <?php else: ?>
                    <i class="bi bi-info-circle-fill text-info" style="font-size: 1.5rem;"></i>
                    <?php endif; ?>
                </div>
                
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 fw-bold">
                                <?php echo e($notification->title); ?>

                                <?php if(!$notification->is_read): ?>
                                <span class="badge bg-primary ms-2">New</span>
                                <?php endif; ?>
                            </h6>
                            <p class="mb-2 text-secondary"><?php echo e($notification->message); ?></p>
                            <?php if($notification->document): ?>
                            <a href="<?php echo e(route('documents.show', $notification->document)); ?>" class="text-decoration-none small text-primary">
                                <i class="bi bi-arrow-right-circle"></i> View Document: <strong><?php echo e($notification->document->document_number); ?></strong>
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="text-end" style="min-width: 150px;">
                            <small class="text-muted d-block mb-2"><?php echo e($notification->created_at->diffForHumans()); ?></small>
                            <div class="d-flex gap-1 justify-content-end">
                                <?php if(!$notification->is_read): ?>
                                <form method="POST" action="<?php echo e(route('notifications.read', $notification)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm btn-primary" title="Mark as read">
                                        <i class="bi bi-check"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" action="<?php echo e(route('notifications.destroy', $notification)); ?>" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this notification?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-5">
                <i class="bi bi-bell-slash" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No notifications yet</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            Showing <?php echo e($notifications->firstItem() ?? 0); ?> to <?php echo e($notifications->lastItem() ?? 0); ?> of <?php echo e($notifications->total()); ?> results
        </div>
        <nav aria-label="Notification pagination">
            <ul class="pagination mb-0">
                
                <?php if($notifications->onFirstPage()): ?>
                    <li class="page-item disabled">
                        <span class="page-link">Previous</span>
                    </li>
                <?php else: ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo e($notifications->previousPageUrl()); ?>" rel="prev">Previous</a>
                    </li>
                <?php endif; ?>

                
                <?php $__currentLoopData = $notifications->getUrlRange(1, $notifications->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $notifications->currentPage()): ?>
                        <li class="page-item active" aria-current="page">
                            <span class="page-link"><?php echo e($page); ?></span>
                        </li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                
                <?php if($notifications->hasMorePages()): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo e($notifications->nextPageUrl()); ?>" rel="next">Next</a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <span class="page-link">Next</span>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>

<script>
// Auto-refresh notification count every 30 seconds
setInterval(function() {
    fetch('<?php echo e(route("notifications.unread-count")); ?>')
        .then(response => response.json())
        .then(data => {
            // Update badge in sidebar if exists
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }
            }
        });
}, 30000);
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\dashboard\lgu-document-tracking\resources\views/notifications/index.blade.php ENDPATH**/ ?>