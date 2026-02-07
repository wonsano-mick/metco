<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {

    // ==================== PUBLIC ROUTES ====================

    // Auth routes (with rate limiting)
    // Route::middleware('throttle:10,1')->prefix('auth')->group(function () {
    //     Route::post('register', [AuthController::class, 'register'])
    //         ->name('api.v1.auth.register');

    //     Route::post('login', [AuthController::class, 'login'])
    //         ->name('api.v1.auth.login');

    //     Route::post('refresh', [AuthController::class, 'refresh'])
    //         ->name('api.v1.auth.refresh');

    //     Route::post('password/reset', [AuthController::class, 'requestPasswordReset'])
    //         ->name('api.v1.auth.password.reset');

    //     Route::post('password/reset/confirm', [AuthController::class, 'resetPassword'])
    //         ->name('api.v1.auth.password.reset.confirm');
    // });

    // Health check (public)
    Route::get('health', function () {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => 'v1',
            'environment' => config('app.env'),
        ]);
    })->name('api.v1.health');


    // ==================== PROTECTED ROUTES ====================

    // Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    //     // Auth (authenticated user)
    //     Route::prefix('auth')->group(function () {
    //         Route::post('logout', [AuthController::class, 'logout'])
    //             ->name('api.v1.auth.logout');

    //         Route::get('profile', [AuthController::class, 'profile'])
    //             ->name('api.v1.auth.profile');

    //         Route::post('two-factor/verify', [AuthController::class, 'twoFactorVerify'])
    //             ->name('api.v1.auth.two-factor.verify');

    //         Route::post('two-factor/enable', [AuthController::class, 'enableTwoFactor'])
    //             ->name('api.v1.auth.two-factor.enable');

    //         Route::post('two-factor/disable', [AuthController::class, 'disableTwoFactor'])
    //             ->name('api.v1.auth.two-factor.disable');

    //         Route::post('password/change', [AuthController::class, 'changePassword'])
    //             ->name('api.v1.auth.password.change');
    //     });

    //     // User routes
    //     Route::prefix('user')->group(function () {
    //         Route::get('devices', [App\Http\Controllers\Api\V1\UserController::class, 'devices'])
    //             ->name('api.v1.user.devices');

    //         Route::delete('devices/{device}', [App\Http\Controllers\Api\V1\UserController::class, 'revokeDevice'])
    //             ->name('api.v1.user.devices.revoke');

    //         Route::get('notifications', [App\Http\Controllers\Api\V1\UserController::class, 'notifications'])
    //             ->name('api.v1.user.notifications');

    //         Route::put('notifications/{notification}/read', [App\Http\Controllers\Api\V1\UserController::class, 'markNotificationRead'])
    //             ->name('api.v1.user.notifications.read');
    //     });

    //     // Account routes
    //     Route::apiResource('accounts', App\Http\Controllers\Api\V1\AccountController::class)
    //         ->except(['destroy'])
    //         ->names([
    //             'index' => 'api.v1.accounts.index',
    //             'store' => 'api.v1.accounts.store',
    //             'show' => 'api.v1.accounts.show',
    //             'update' => 'api.v1.accounts.update',
    //         ]);

    //     Route::prefix('accounts/{account}')->group(function () {
    //         Route::get('balance', [App\Http\Controllers\Api\V1\AccountController::class, 'balance'])
    //             ->name('api.v1.accounts.balance');

    //         Route::get('statement', [App\Http\Controllers\Api\V1\AccountController::class, 'statement'])
    //             ->name('api.v1.accounts.statement');

    //         Route::post('freeze', [App\Http\Controllers\Api\V1\AccountController::class, 'freeze'])
    //             ->name('api.v1.accounts.freeze')
    //             ->middleware('can:manage,account');
    //     });

    //     // Transaction routes
    //     Route::apiResource('transactions', App\Http\Controllers\Api\V1\TransactionController::class)
    //         ->except(['update', 'destroy'])
    //         ->names([
    //             'index' => 'api.v1.transactions.index',
    //             'store' => 'api.v1.transactions.store',
    //             'show' => 'api.v1.transactions.show',
    //         ]);

    //     Route::prefix('transactions')->group(function () {
    //         Route::post('transfer', [App\Http\Controllers\Api\V1\TransactionController::class, 'transfer'])
    //             ->name('api.v1.transactions.transfer');

    //         Route::post('deposit', [App\Http\Controllers\Api\V1\TransactionController::class, 'deposit'])
    //             ->name('api.v1.transactions.deposit');

    //         Route::post('withdraw', [App\Http\Controllers\Api\V1\TransactionController::class, 'withdraw'])
    //             ->name('api.v1.transactions.withdraw');

    //         Route::post('{transaction}/cancel', [App\Http\Controllers\Api\V1\TransactionController::class, 'cancel'])
    //             ->name('api.v1.transactions.cancel');

    //         Route::get('{transaction}/receipt', [App\Http\Controllers\Api\V1\TransactionController::class, 'receipt'])
    //             ->name('api.v1.transactions.receipt');
    //     });


    //     // ==================== ADMIN ROUTES ====================

    //     Route::middleware(['role:admin'])->prefix('admin')->group(function () {

    //         // Admin users management
    //         Route::apiResource('users', App\Http\Controllers\Api\V1\AdminController::class)
    //             ->only(['index', 'show', 'update'])
    //             ->names([
    //                 'index' => 'api.v1.admin.users.index',
    //                 'show' => 'api.v1.admin.users.show',
    //                 'update' => 'api.v1.admin.users.update',
    //             ]);

    //         Route::prefix('users/{user}')->group(function () {
    //             Route::post('activate', [App\Http\Controllers\Api\V1\AdminController::class, 'activateUser'])
    //                 ->name('api.v1.admin.users.activate');

    //             Route::post('deactivate', [App\Http\Controllers\Api\V1\AdminController::class, 'deactivateUser'])
    //                 ->name('api.v1.admin.users.deactivate');

    //             Route::post('lock', [App\Http\Controllers\Api\V1\AdminController::class, 'lockUser'])
    //                 ->name('api.v1.admin.users.lock');

    //             Route::post('unlock', [App\Http\Controllers\Api\V1\AdminController::class, 'unlockUser'])
    //                 ->name('api.v1.admin.users.unlock');
    //         });

    //         // Admin accounts management
    //         Route::prefix('accounts')->group(function () {
    //             Route::get('/', [App\Http\Controllers\Api\V1\AdminController::class, 'accounts'])
    //                 ->name('api.v1.admin.accounts.index');

    //             Route::post('{account}/adjust', [App\Http\Controllers\Api\V1\AdminController::class, 'adjustBalance'])
    //                 ->name('api.v1.admin.accounts.adjust')
    //                 ->middleware('can:manage,account');

    //             Route::post('{account}/force-close', [App\Http\Controllers\Api\V1\AdminController::class, 'forceCloseAccount'])
    //                 ->name('api.v1.admin.accounts.force-close')
    //                 ->middleware('can:manage,account');
    //         });

    //         // Admin transactions management
    //         Route::prefix('transactions')->group(function () {
    //             Route::get('/', [App\Http\Controllers\Api\V1\AdminController::class, 'transactions'])
    //                 ->name('api.v1.admin.transactions.index');

    //             Route::post('{transaction}/reverse', [App\Http\Controllers\Api\V1\AdminController::class, 'reverseTransaction'])
    //                 ->name('api.v1.admin.transactions.reverse');

    //             Route::get('fraud-check', [App\Http\Controllers\Api\V1\AdminController::class, 'fraudCheck'])
    //                 ->name('api.v1.admin.transactions.fraud-check');
    //         });

    //         // Audit logs
    //         Route::prefix('audit')->group(function () {
    //             Route::get('logs', [App\Http\Controllers\Api\V1\AdminController::class, 'auditLogs'])
    //                 ->name('api.v1.admin.audit.logs');

    //             Route::get('security', [App\Http\Controllers\Api\V1\AdminController::class, 'securityLogs'])
    //                 ->name('api.v1.admin.audit.security');
    //         });

    //         // Reports
    //         Route::prefix('reports')->group(function () {
    //             Route::get('daily', [App\Http\Controllers\Api\V1\AdminController::class, 'dailyReport'])
    //                 ->name('api.v1.admin.reports.daily');

    //             Route::get('monthly', [App\Http\Controllers\Api\V1\AdminController::class, 'monthlyReport'])
    //                 ->name('api.v1.admin.reports.monthly');

    //             Route::get('transactions', [App\Http\Controllers\Api\V1\AdminController::class, 'transactionReport'])
    //                 ->name('api.v1.admin.reports.transactions');

    //             Route::get('users', [App\Http\Controllers\Api\V1\AdminController::class, 'userReport'])
    //                 ->name('api.v1.admin.reports.users');
    //         });
    //     });
    // });
});
