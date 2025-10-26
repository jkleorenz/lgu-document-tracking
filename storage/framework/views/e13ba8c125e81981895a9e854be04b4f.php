<?php $__env->startSection('title', 'Administrator Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Administrator Dashboard</h2>
        <a href="<?php echo e(route('documents.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Document
        </a>
    </div>

    <!-- Document Statistics -->
    <div class="mb-3">
        <h5 class="text-muted mb-3"><i class="bi bi-file-text"></i> Document Overview</h5>
    </div>
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <a href="<?php echo e(route('documents.index', ['status' => 'Pending'])); ?>" class="text-decoration-none">
                <div class="card stat-card h-100 clickable-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon bg-success bg-opacity-10">
                                <i class="bi bi-check-circle text-success"></i>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                        </div>
                        <h6 class="text-muted mb-2">Active Documents</h6>
                        <h2 class="fw-bold mb-0 text-dark"><?php echo e($activeDocuments); ?></h2>
                        <small class="text-success"><i class="bi bi-arrow-right-circle"></i> View active</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <a href="<?php echo e(route('documents.index', ['priority' => '1'])); ?>" class="text-decoration-none">
                <div class="card stat-card h-100 clickable-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon bg-danger bg-opacity-10">
                                <i class="bi bi-exclamation-triangle text-danger"></i>
                            </div>
                            <?php if($priorityDocuments > 0): ?>
                            <span class="badge badge-priority">Urgent!</span>
                            <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">None</span>
                            <?php endif; ?>
                        </div>
                        <h6 class="text-muted mb-2">Priority Documents</h6>
                        <h2 class="fw-bold mb-0 text-dark"><?php echo e($priorityDocuments); ?></h2>
                        <small class="text-danger"><i class="bi bi-arrow-right-circle"></i> View priority</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <a href="<?php echo e(route('archive.index')); ?>" class="text-decoration-none">
                <div class="card stat-card h-100 clickable-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon bg-secondary bg-opacity-10">
                                <i class="bi bi-archive text-secondary"></i>
                            </div>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Stored</span>
                        </div>
                        <h6 class="text-muted mb-2">Archived</h6>
                        <h2 class="fw-bold mb-0 text-dark"><?php echo e($archivedDocuments); ?></h2>
                        <small class="text-secondary"><i class="bi bi-arrow-right-circle"></i> View archive</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- User & System Statistics -->
    <div class="mb-3">
        <h5 class="text-muted mb-3"><i class="bi bi-people"></i> User Management</h5>
    </div>
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card stat-card h-100 <?php echo e($pendingVerifications > 0 ? 'border-warning' : ''); ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="stat-icon bg-warning bg-opacity-10">
                                    <i class="bi bi-person-check text-warning"></i>
                                </div>
                                <h6 class="text-muted mb-0">Pending Verifications</h6>
                            </div>
                            <h2 class="fw-bold mb-0"><?php echo e($pendingVerifications); ?></h2>
                            <?php if($pendingVerifications > 0): ?>
                            <p class="text-warning mb-0 mt-2"><small><i class="bi bi-exclamation-circle"></i> Action required</small></p>
                            <?php else: ?>
                            <p class="text-success mb-0 mt-2"><small><i class="bi bi-check-circle"></i> All users verified</small></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if($pendingVerifications > 0): ?>
                    <a href="<?php echo e(route('users.pending')); ?>" class="btn btn-warning w-100 mt-2">
                        <i class="bi bi-arrow-right-circle"></i> Review Pending Users
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <a href="<?php echo e(route('users.index')); ?>" class="text-decoration-none">
                <div class="card stat-card h-100 clickable-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon bg-info bg-opacity-10">
                                <i class="bi bi-people text-info"></i>
                            </div>
                            <span class="badge bg-info bg-opacity-10 text-info">System</span>
                        </div>
                        <h6 class="text-muted mb-2">Total Users</h6>
                        <h2 class="fw-bold mb-0 text-dark"><?php echo e($totalUsers); ?></h2>
                        <small class="text-info"><i class="bi bi-arrow-right-circle"></i> Manage users</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <a href="<?php echo e(route('documents.index')); ?>" class="text-decoration-none">
                <div class="card stat-card h-100 clickable-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon bg-success bg-opacity-10">
                                <i class="bi bi-building text-success"></i>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                        </div>
                        <h6 class="text-muted mb-2">Departments</h6>
                        <h2 class="fw-bold mb-0 text-dark"><?php echo e($totalDepartments); ?></h2>
                        <small class="text-success"><i class="bi bi-arrow-right-circle"></i> View by dept</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Document Status Distribution -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Document Status Distribution</h5>
                </div>
                <div class="card-body">
                    <?php $__currentLoopData = $documentsByStatus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?php echo e($status); ?></span>
                            <span class="fw-bold"><?php echo e($count); ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?php echo e($totalDocuments > 0 ? ($count / $totalDocuments) * 100 : 0); ?>%">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Documents</h5>
                    <a href="<?php echo e(route('documents.index')); ?>" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Document #</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $recentDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo e(route('documents.show', $document)); ?>" class="text-decoration-none">
                                            <?php echo e($document->document_number); ?>

                                        </a>
                                    </td>
                                    <td>
                                        <?php echo e(Str::limit($document->title, 30)); ?>

                                        <?php if($document->is_priority): ?>
                                        <span class="badge badge-priority">PRIORITY</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo e($document->status == 'Approved' ? 'success' : ($document->status == 'Pending' ? 'warning' : 'info')); ?>">
                                            <?php echo e($document->status); ?>

                                        </span>
                                    </td>
                                    <td><small><?php echo e($document->created_at->diffForHumans()); ?></small></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No documents yet</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($pendingUsers->count() > 0): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-person-exclamation"></i> Pending User Verifications</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Role</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $pendingUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($user->name); ?></td>
                                    <td><?php echo e($user->email); ?></td>
                                    <td><?php echo e($user->department ? $user->department->name : 'N/A'); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo e($user->roles->first() ? $user->roles->first()->name : 'No Role'); ?></span></td>
                                    <td><small><?php echo e($user->created_at->diffForHumans()); ?></small></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <form method="POST" action="<?php echo e(route('users.verify', $user)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check-circle"></i> Verify
                                                </button>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('users.reject', $user)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure you want to reject this user?')">
                                                    <i class="bi bi-x-circle"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\dashboard\lgu-document-tracking\resources\views/dashboard/admin.blade.php ENDPATH**/ ?>