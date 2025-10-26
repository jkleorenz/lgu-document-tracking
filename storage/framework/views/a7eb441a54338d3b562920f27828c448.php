

<?php $__env->startSection('title', 'User Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-people"></i> User Management</h2>
        <div>
            <?php if($pendingCount > 0): ?>
            <a href="<?php echo e(route('users.pending')); ?>" class="btn btn-warning me-2">
                <i class="bi bi-person-exclamation"></i> Pending Verifications
                <span class="badge bg-dark"><?php echo e($pendingCount); ?></span>
            </a>
            <?php endif; ?>
            <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Add New User
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('users.index')); ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="verified" <?php echo e(request('status') == 'verified' ? 'selected' : ''); ?>>Verified</option>
                            <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                            <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select">
                            <option value="">All Roles</option>
                            <option value="Administrator" <?php echo e(request('role') == 'Administrator' ? 'selected' : ''); ?>>Administrator</option>
                            <option value="LGU Staff" <?php echo e(request('role') == 'LGU Staff' ? 'selected' : ''); ?>>LGU Staff</option>
                            <option value="Department Head" <?php echo e(request('role') == 'Department Head' ? 'selected' : ''); ?>>Department Head</option>
                        </select>
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

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($user->name); ?></td>
                            <td><?php echo e($user->email); ?></td>
                            <td><?php echo e($user->department->name ?? 'N/A'); ?></td>
                            <td><span class="badge bg-primary"><?php echo e($user->roles->first()->name ?? 'No Role'); ?></span></td>
                            <td>
                                <?php if($user->status == 'verified'): ?>
                                <span class="badge bg-success">Verified</span>
                                <?php elseif($user->status == 'pending'): ?>
                                <span class="badge bg-warning">Pending</span>
                                <?php else: ?>
                                <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td><small><?php echo e($user->created_at->format('M d, Y')); ?></small></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo e(route('users.show', $user)); ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('users.edit', $user)); ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if($user->id != auth()->id()): ?>
                                    <form method="POST" action="<?php echo e(route('users.destroy', $user)); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No users found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <?php echo e($users->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\dashboard\lgu-document-tracking\resources\views/users/index.blade.php ENDPATH**/ ?>