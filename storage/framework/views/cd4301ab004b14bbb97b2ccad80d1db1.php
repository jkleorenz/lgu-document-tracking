

<?php $__env->startSection('title', 'Login - LGU Document Tracking'); ?>

<?php $__env->startSection('content'); ?>
<style>
    body {
        background: #f1f5f9;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
    }
    .login-header {
        padding: 40px 40px 30px;
        text-align: center;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(249, 250, 251, 0.9) 100%);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    .login-body {
        padding: 40px;
    }
    .brand-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto 20px;
        box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3);
    }
    .form-floating {
        margin-bottom: 20px;
    }
    .form-floating .form-control {
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        padding: 1rem 0.75rem;
        height: calc(3.5rem + 2px);
    }
    .form-floating label {
        padding: 1rem 0.75rem;
    }
    .btn-login {
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        border: none;
        border-radius: 12px;
        padding: 14px;
        font-weight: 600;
        font-size: 1rem;
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.4);
        transition: all 0.3s;
    }
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 24px rgba(37, 99, 235, 0.5);
    }
    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 24px 0;
    }
    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e2e8f0;
    }
    .divider span {
        padding: 0 10px;
        color: #94a3b8;
        font-size: 0.875rem;
    }
</style>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5 col-lg-4">
            <div class="login-card">
                <div class="login-header">
                    <div class="brand-icon">
                        <i class="bi bi-file-text text-white"></i>
                    </div>
                    <h2 class="fw-bold mb-2" style="color: #0f172a;">LGU DocTrack</h2>
                    <p class="text-muted mb-0">Document Tracking System</p>
                </div>

                <div class="login-body">
                    <?php if(session('success')): ?>
                    <div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px;">
                        <i class="bi bi-check-circle-fill"></i> <?php echo e(session('success')); ?>

                    </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo e(route('login')); ?>">
                        <?php echo csrf_field(); ?>

                        <div class="form-floating">
                            <input type="email" 
                                   class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="email" 
                                   name="email" 
                                   placeholder="name@example.com"
                                   value="<?php echo e(old('email')); ?>" 
                                   required 
                                   autofocus>
                            <label for="email"><i class="bi bi-envelope"></i> Email Address</label>
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-floating">
                            <input type="password" 
                                   class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="password" 
                                   name="password"
                                   placeholder="Password" 
                                   required>
                            <label for="password"><i class="bi bi-lock"></i> Password</label>
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <button type="submit" class="btn btn-login btn-primary w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right"></i> Sign In
                        </button>

                        <div class="divider">
                            <span>OR</span>
                        </div>

                        <div class="text-center">
                            <p class="mb-0 text-muted">
                                Don't have an account? 
                                <a href="<?php echo e(route('register')); ?>" class="text-decoration-none fw-semibold" style="color: #2563eb;">
                                    Create Account
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <small class="text-white-50">&copy; <?php echo e(date('Y')); ?> LGU Document Tracking System. All rights reserved.</small>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\dashboard\lgu-document-tracking\resources\views/auth/login.blade.php ENDPATH**/ ?>