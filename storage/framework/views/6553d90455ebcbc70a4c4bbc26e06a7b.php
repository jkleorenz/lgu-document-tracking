<?php $__env->startSection('title', 'Department Head Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Department Head Dashboard</h2>
            <p class="text-muted mb-0"><?php echo e(auth()->user()->department ? auth()->user()->department->name : 'No Department'); ?></p>
        </div>
        <a href="<?php echo e(route('scan.index')); ?>" class="btn btn-primary">
            <i class="bi bi-qr-code-scan"></i> Scan QR Code
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <a href="<?php echo e(route('documents.index', ['department' => auth()->user()->department_id])); ?>" class="text-decoration-none">
                <div class="card stat-card clickable-card" style="border-left-color: #0d6efd;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Department Documents</h6>
                                <h2 class="fw-bold mb-0 text-dark"><?php echo e($departmentDocuments); ?></h2>
                                <small class="text-primary"><i class="bi bi-arrow-right-circle"></i> View all</small>
                            </div>
                            <div class="text-primary" style="font-size: 2.5rem;">
                                <i class="bi bi-folder"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="<?php echo e(route('documents.index', ['status' => 'Under Review', 'department' => auth()->user()->department_id])); ?>" class="text-decoration-none">
                <div class="card stat-card clickable-card" style="border-left-color: #ffc107;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">For Review</h6>
                                <h2 class="fw-bold mb-0 text-dark"><?php echo e($forReview); ?></h2>
                                <small class="text-warning"><i class="bi bi-arrow-right-circle"></i> View review</small>
                            </div>
                            <div class="text-warning" style="font-size: 2.5rem;">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="<?php echo e(route('documents.index', ['priority' => '1', 'department' => auth()->user()->department_id])); ?>" class="text-decoration-none">
                <div class="card stat-card clickable-card" style="border-left-color: #dc3545;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Priority</h6>
                                <h2 class="fw-bold mb-0 text-dark"><?php echo e($priorityDocuments); ?></h2>
                                <small class="text-danger"><i class="bi bi-arrow-right-circle"></i> View priority</small>
                            </div>
                            <div class="text-danger" style="font-size: 2.5rem;">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="<?php echo e(route('documents.index', ['department' => auth()->user()->department_id])); ?>" class="text-decoration-none">
                <div class="card stat-card clickable-card" style="border-left-color: #198754;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Handling</h6>
                                <h2 class="fw-bold mb-0 text-dark"><?php echo e($handlingDocuments); ?></h2>
                                <small class="text-success"><i class="bi bi-arrow-right-circle"></i> View handling</small>
                            </div>
                            <div class="text-success" style="font-size: 2.5rem;">
                                <i class="bi bi-person-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?php echo e(route('scan.index')); ?>" class="quick-action-btn">
                                <div class="quick-action-icon bg-primary bg-opacity-10">
                                    <i class="bi bi-qr-code-scan text-primary"></i>
                                </div>
                                <span class="text-primary">Scan QR Code</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo e(route('documents.index')); ?>?department=<?php echo e(auth()->user()->department_id); ?>" 
                               class="quick-action-btn">
                                <div class="quick-action-icon bg-info bg-opacity-10">
                                    <i class="bi bi-folder text-info"></i>
                                </div>
                                <span class="text-info">Department Documents</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo e(route('archive.index')); ?>" class="quick-action-btn">
                                <div class="quick-action-icon bg-secondary bg-opacity-10">
                                    <i class="bi bi-archive text-secondary"></i>
                                </div>
                                <span class="text-secondary">Archived Documents</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo e(route('notifications.index')); ?>" class="quick-action-btn">
                                <div class="quick-action-icon bg-warning bg-opacity-10">
                                    <i class="bi bi-bell text-warning"></i>
                                </div>
                                <span class="text-warning">Notifications</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Department Documents -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Department Documents</h5>
                    <a href="<?php echo e(route('documents.index')); ?>" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Document Number</th>
                                    <th>Title</th>
                                    <th>Created By</th>
                                    <th>Status</th>
                                    <th>Current Handler</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $recentDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="<?php echo e($document->is_priority ? 'table-warning' : ''); ?>">
                                    <td>
                                        <strong><?php echo e($document->document_number); ?></strong>
                                        <?php if($document->is_priority): ?>
                                        <br><span class="badge badge-priority">PRIORITY</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e(Str::limit($document->title, 40)); ?></td>
                                    <td><?php echo e($document->creator ? $document->creator->name : 'Unknown'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo e($document->status == 'Approved' ? 'success' : ($document->status == 'Pending' ? 'warning' : ($document->status == 'Rejected' ? 'danger' : 'info'))); ?>">
                                            <?php echo e($document->status); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php if($document->currentHandler): ?>
                                        <?php echo e($document->currentHandler->name); ?>

                                        <?php else: ?>
                                        <span class="text-muted">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?php echo e($document->created_at->format('M d, Y')); ?></small></td>
                                    <td>
                                        <a href="<?php echo e(route('documents.show', $document)); ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                        <p class="mb-0">No documents in your department yet</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\dashboard\lgu-document-tracking\resources\views/dashboard/department-head.blade.php ENDPATH**/ ?>