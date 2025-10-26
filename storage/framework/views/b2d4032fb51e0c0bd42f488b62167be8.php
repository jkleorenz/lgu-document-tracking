

<?php $__env->startSection('title', 'Archived Documents'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="mb-4">
        <h2 class="fw-bold"><i class="bi bi-archive"></i> Archived Documents</h2>
        <p class="text-muted">View and manage archived documents</p>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('archive.index')); ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search archived documents..." value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="from_date" class="form-control" placeholder="From Date" value="<?php echo e(request('from_date')); ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="to_date" class="form-control" placeholder="To Date" value="<?php echo e(request('to_date')); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Archived Documents Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-archive"></i> Archived Documents 
                <span class="badge bg-secondary"><?php echo e($archivedDocuments->total()); ?></span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Document #</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Department</th>
                            <th>Created By</th>
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $archivedDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e($document->document_number); ?></strong></td>
                            <td><?php echo e(Str::limit($document->title, 50)); ?></td>
                            <td><span class="badge bg-secondary"><?php echo e($document->document_type); ?></span></td>
                            <td><?php echo e($document->department->code); ?></td>
                            <td><?php echo e($document->creator->name); ?></td>
                            <td>
                                <small><?php echo e($document->archived_at->format('M d, Y')); ?></small><br>
                                <small class="text-muted"><?php echo e($document->archived_at->diffForHumans()); ?></small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?php echo e(route('archive.show', $document)); ?>" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('archive-documents')): ?>
                                    <form method="POST" action="<?php echo e(route('archive.restore', $document)); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-success" title="Restore" onclick="return confirm('Restore this document from archive?')">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'Administrator')): ?>
                                    <form method="POST" action="<?php echo e(route('archive.destroy', $document)); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger" title="Permanently Delete" onclick="return confirm('⚠️ PERMANENTLY DELETE this document?\n\nThis action CANNOT be undone!\n\nDocument: <?php echo e($document->document_number); ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-archive" style="font-size: 3rem;"></i>
                                <p class="mb-0">No archived documents found</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <?php echo e($archivedDocuments->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\dashboard\lgu-document-tracking\resources\views/archive/index.blade.php ENDPATH**/ ?>