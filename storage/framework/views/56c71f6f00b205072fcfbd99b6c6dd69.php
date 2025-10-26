<?php $__env->startSection('title', 'Documents'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-file-earmark-text"></i> Documents</h2>
        <?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'Administrator')): ?>
        <a href="<?php echo e(route('documents.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Document
        </a>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('documents.index')); ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search documents..." value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Pending" <?php echo e(request('status') == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                            <option value="Received" <?php echo e(request('status') == 'Received' ? 'selected' : ''); ?>>Received</option>
                            <option value="Under Review" <?php echo e(request('status') == 'Under Review' ? 'selected' : ''); ?>>Under Review</option>
                            <option value="Forwarded" <?php echo e(request('status') == 'Forwarded' ? 'selected' : ''); ?>>Forwarded</option>
                            <option value="Approved" <?php echo e(request('status') == 'Approved' ? 'selected' : ''); ?>>Approved</option>
                            <option value="Rejected" <?php echo e(request('status') == 'Rejected' ? 'selected' : ''); ?>>Rejected</option>
                        </select>
                    </div>
                    <?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'Administrator')): ?>
                    <div class="col-md-2">
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($dept->id); ?>" <?php echo e(request('department') == $dept->id ? 'selected' : ''); ?>>
                                <?php echo e($dept->name); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="priority" id="priority" value="1" <?php echo e(request('priority') ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="priority">
                                Priority Only
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-info">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="<?php echo e(route('documents.index')); ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Document #</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Current Handler</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="<?php echo e($document->is_priority ? 'table-warning' : ''); ?>">
                            <td>
                                <strong><?php echo e($document->document_number); ?></strong>
                                <?php if($document->is_priority): ?>
                                <br><span class="badge badge-priority">PRIORITY</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e(Str::limit($document->title, 50)); ?></td>
                            <td><span class="badge bg-secondary"><?php echo e($document->document_type); ?></span></td>
                            <td><?php echo e($document->department ? $document->department->code : 'N/A'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo e($document->status == 'Approved' ? 'success' : ($document->status == 'Received' ? 'success' : ($document->status == 'Pending' ? 'warning' : ($document->status == 'Rejected' ? 'danger' : 'info')))); ?>">
                                    <?php echo e($document->status); ?>

                                </span>
                            </td>
                            <td><?php echo e($document->creator ? $document->creator->name : 'Unknown'); ?></td>
                            <td>
                                <?php if($document->currentHandler): ?>
                                <?php echo e($document->currentHandler->name); ?>

                                <?php else: ?>
                                <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td><small><?php echo e($document->created_at->format('M d, Y')); ?></small></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?php echo e(route('documents.show', $document)); ?>" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-documents')): ?>
                                    <?php if($document->created_by == auth()->id() || auth()->user()->hasRole('Administrator')): ?>
                                    <a href="<?php echo e(route('documents.edit', $document)); ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                    <a href="<?php echo e(route('documents.print-qr', $document)); ?>" class="btn btn-sm btn-secondary" title="Print QR" target="_blank">
                                        <i class="bi bi-qr-code"></i>
                                    </a>
                                    <?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'Administrator|Department Head')): ?>
                                    <?php if($document->status != 'Archived'): ?>
                                    <form method="POST" action="<?php echo e(route('documents.archive', $document)); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-dark" title="Archive" onclick="return confirm('Archive this document?')">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mb-0">No documents found</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <?php echo e($documents->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\dashboard\lgu-document-tracking\resources\views/documents/index.blade.php ENDPATH**/ ?>