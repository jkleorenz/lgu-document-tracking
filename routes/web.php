<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected Routes (Require Authentication and Verified Account)
Route::middleware(['auth'])->group(function () {
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Profile & Settings
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/picture', [ProfileController::class, 'updateProfilePicture'])->name('profile.picture');
    Route::delete('/profile/picture', [ProfileController::class, 'removeProfilePicture'])->name('profile.picture.remove');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::put('/settings/password', [ProfileController::class, 'updatePassword'])->name('settings.password');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Document Management
    Route::resource('documents', DocumentController::class);
    Route::post('/documents/{document}/priority', [DocumentController::class, 'setPriority'])
        ->name('documents.priority');
    Route::post('/documents/{document}/archive', [DocumentController::class, 'archive'])
        ->name('documents.archive');
    Route::post('/documents/{document}/approve', [DocumentController::class, 'approve'])
        ->name('documents.approve');
    Route::post('/documents/{document}/reject', [DocumentController::class, 'rejectDocument'])
        ->name('documents.reject');
    Route::get('/documents/{document}/print-qr', [DocumentController::class, 'printQRCode'])
        ->name('documents.print-qr');
    Route::get('/documents/{document}/timeline', [DocumentController::class, 'timeline'])
        ->name('documents.timeline');
    Route::get('/documents/{document}/report', [DocumentController::class, 'generateReport'])
        ->name('documents.report');
    
    // QR Code Scanner
    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    Route::post('/scan', [ScanController::class, 'scan'])->name('scan.process');
    Route::post('/scan/quick-update', [ScanController::class, 'quickUpdate'])
        ->name('scan.quick-update');
    Route::post('/scan/complete', [ScanController::class, 'complete'])->name('scan.complete');
    Route::post('/scan/return', [ScanController::class, 'returnDocument'])->name('scan.return');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');
    
    // AJAX routes for notifications
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unread-count');
    Route::get('/api/notifications/recent', [NotificationController::class, 'recent'])
        ->name('notifications.recent');
    
    // Archive
    Route::get('/archive', [ArchiveController::class, 'index'])->name('archive.index');
    Route::get('/archive/{document}', [ArchiveController::class, 'show'])->name('archive.show');
    Route::post('/archive/{document}/restore', [ArchiveController::class, 'restore'])
        ->name('archive.restore');
    Route::delete('/archive/{document}', [ArchiveController::class, 'destroy'])
        ->name('archive.destroy');
    
    // User Management (Admin Only)
    Route::middleware(['role:Administrator'])->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/users-pending', [UserController::class, 'pendingVerifications'])
            ->name('users.pending');
        Route::post('/users/{user}/verify', [UserController::class, 'verify'])
            ->name('users.verify');
        Route::post('/users/{user}/reject', [UserController::class, 'reject'])
            ->name('users.reject');
    });
});

