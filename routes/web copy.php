<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\TransactionController;
use App\Livewire\Users\UserIndex;

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

// ==================== PUBLIC ROUTES ====================

// Landing page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Email verification
// Route::get('/email/verify', [AuthController::class, 'showVerifyEmail'])->middleware('auth')->name('verification.notice');
// Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['auth', 'signed'])->name('verification.verify');
// Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');


// ==================== PROTECTED ROUTES ====================

Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', UserIndex::class)->name('index');
    // Route::get('/create', UserForm::class)->name('create');
    // Route::get('/{user}', UserShow::class)->name('show');
    // Route::get('/{user}/edit', UserForm::class)->name('edit');

    // Two-Factor Authentication
    // Route::get('/two-factor', [AuthController::class, 'showTwoFactorForm'])->name('two-factor.show');
    // Route::post('/two-factor', [AuthController::class, 'enableTwoFactor'])->name('two-factor.enable');
    // Route::post('/two-factor/verify', [AuthController::class, 'verifyTwoFactor'])->name('two-factor.verify');
    // Route::delete('/two-factor', [AuthController::class, 'disableTwoFactor'])->name('two-factor.disable');

    // // Profile
    // Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.show');
    // Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    // Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // // Security
    // Route::get('/security', [AuthController::class, 'showSecurity'])->name('security');
    // Route::get('/security/devices', [AuthController::class, 'showDevices'])->name('security.devices');
    // Route::delete('/security/devices/{device}', [AuthController::class, 'revokeDevice'])->name('security.devices.revoke');

    // Accounts (Web interface)
    // Route::prefix('accounts')->group(function () {
    //     Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
    //     Route::get('/create', [AccountController::class, 'create'])->name('accounts.create');
    //     Route::post('/', [AccountController::class, 'store'])->name('accounts.store');
    //     Route::get('/{account}', [AccountController::class, 'show'])->name('accounts.show');
    //     Route::get('/{account}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
    //     Route::put('/{account}', [AccountController::class, 'update'])->name('accounts.update');
    //     Route::get('/{account}/statement', [AccountController::class, 'statement'])->name('accounts.statement');
    // });

    // Transactions (Web interface)
    // Route::prefix('transactions')->group(function () {
    //     Route::get('/', [TransactionController::class, 'index'])->name('transactions.index');
    //     Route::get('/create', [TransactionController::class, 'create'])->name('transactions.create');
    //     Route::post('/', [TransactionController::class, 'store'])->name('transactions.store');
    //     Route::get('/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    //     Route::get('/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');

    //     // Specific transaction types
    //     Route::get('/transfer', [TransactionController::class, 'transferForm'])->name('transactions.transfer.form');
    //     Route::post('/transfer', [TransactionController::class, 'transfer'])->name('transactions.transfer');

    //     Route::get('/deposit', [TransactionController::class, 'depositForm'])->name('transactions.deposit.form');
    //     Route::post('/deposit', [TransactionController::class, 'deposit'])->name('transactions.deposit');

    //     Route::get('/withdraw', [TransactionController::class, 'withdrawForm'])->name('transactions.withdraw.form');
    //     Route::post('/withdraw', [TransactionController::class, 'withdraw'])->name('transactions.withdraw');
    // });

    // Reports
    // Route::prefix('reports')->group(function () {
    //     Route::get('/', [DashboardController::class, 'reports'])->name('reports.index');
    //     Route::get('/monthly', [DashboardController::class, 'monthlyReport'])->name('reports.monthly');
    //     Route::get('/yearly', [DashboardController::class, 'yearlyReport'])->name('reports.yearly');
    // });

    // Settings
    // Route::get('/settings', [AuthController::class, 'showSettings'])->name('settings');
    // Route::put('/settings/notifications', [AuthController::class, 'updateNotificationSettings'])->name('settings.notifications');

    // Logout
    // Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});


// ==================== ADMIN ROUTES ====================

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Admin Dashboard
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

    // User Management
    // Route::prefix('users')->group(function () {
    //     Route::get('/', [\App\Http\Controllers\Web\AdminController::class, 'users'])->name('users.index');
    //     Route::get('/{user}', [\App\Http\Controllers\Web\AdminController::class, 'userShow'])->name('users.show');
    //     Route::get('/{user}/edit', [\App\Http\Controllers\Web\AdminController::class, 'userEdit'])->name('users.edit');
    //     Route::put('/{user}', [\App\Http\Controllers\Web\AdminController::class, 'userUpdate'])->name('users.update');
    //     Route::post('/{user}/activate', [\App\Http\Controllers\Web\AdminController::class, 'userActivate'])->name('users.activate');
    //     Route::post('/{user}/deactivate', [\App\Http\Controllers\Web\AdminController::class, 'userDeactivate'])->name('users.deactivate');
    //     Route::post('/{user}/lock', [\App\Http\Controllers\Web\AdminController::class, 'userLock'])->name('users.lock');
    //     Route::post('/{user}/unlock', [\App\Http\Controllers\Web\AdminController::class, 'userUnlock'])->name('users.unlock');
    // });

    // Account Management
    // Route::prefix('accounts')->group(function () {
    //     Route::get('/', [\App\Http\Controllers\Web\AdminController::class, 'accounts'])->name('accounts.index');
    //     Route::get('/{account}', [\App\Http\Controllers\Web\AdminController::class, 'accountShow'])->name('accounts.show');
    //     Route::post('/{account}/adjust', [\App\Http\Controllers\Web\AdminController::class, 'accountAdjust'])->name('accounts.adjust');
    //     Route::post('/{account}/close', [\App\Http\Controllers\Web\AdminController::class, 'accountClose'])->name('accounts.close');
    // });

    // Transaction Management
    // Route::prefix('transactions')->group(function () {
    //     Route::get('/', [\App\Http\Controllers\Web\AdminController::class, 'transactions'])->name('transactions.index');
    //     Route::get('/{transaction}', [\App\Http\Controllers\Web\AdminController::class, 'transactionShow'])->name('transactions.show');
    //     Route::post('/{transaction}/reverse', [\App\Http\Controllers\Web\AdminController::class, 'transactionReverse'])->name('transactions.reverse');
    // });

    // Audit Logs
    // Route::prefix('audit')->group(function () {
    //     Route::get('/logs', [\App\Http\Controllers\Web\AdminController::class, 'auditLogs'])->name('audit.logs');
    //     Route::get('/security', [\App\Http\Controllers\Web\AdminController::class, 'securityLogs'])->name('audit.security');
    // });

    // Reports
    // Route::prefix('reports')->group(function () {
    //     Route::get('/', [\App\Http\Controllers\Web\AdminController::class, 'reports'])->name('reports.index');
    //     Route::get('/daily', [\App\Http\Controllers\Web\AdminController::class, 'dailyReport'])->name('reports.daily');
    //     Route::get('/monthly', [\App\Http\Controllers\Web\AdminController::class, 'monthlyReport'])->name('reports.monthly');
    //     Route::get('/fraud', [\App\Http\Controllers\Web\AdminController::class, 'fraudReport'])->name('reports.fraud');
    // });

    // System Settings
    // Route::prefix('settings')->group(function () {
    //     Route::get('/', [\App\Http\Controllers\Web\AdminController::class, 'settings'])->name('settings.index');
    //     Route::put('/general', [\App\Http\Controllers\Web\AdminController::class, 'updateGeneralSettings'])->name('settings.general');
    //     Route::put('/security', [\App\Http\Controllers\Web\AdminController::class, 'updateSecuritySettings'])->name('settings.security');
    //     Route::put('/notifications', [\App\Http\Controllers\Web\AdminController::class, 'updateNotificationSettings'])->name('settings.notifications');
    // });
});


// ==================== HEALTH CHECK ====================

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'service' => 'web',
    ]);
});

// require __DIR__ . '/auth.php';

// ==================== FALLBACK ROUTES ====================

// 404 Page
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
