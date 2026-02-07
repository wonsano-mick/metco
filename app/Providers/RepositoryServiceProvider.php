<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Repositories\AccountRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Repositories\AccountTypeRepositoryInterface;
use App\Domain\Repositories\TransactionRepositoryInterface;
use App\Infrastructure\Repositories\AccountRepository;
use App\Infrastructure\Repositories\UserRepository;
use App\Infrastructure\Repositories\AccountTypeRepository;
use App\Infrastructure\Repositories\TransactionRepository;
use App\Application\Services\AccountService;
use App\Application\Services\TransactionService;
use App\Application\Services\AuthService;
use App\Application\Services\AuditService;
use App\Domain\Services\TransferService;
use App\Infrastructure\Services\AuthenticationService;
use App\Infrastructure\Services\TokenService;
use App\Infrastructure\Services\TwoFactorService;
use App\Infrastructure\Repositories\AuditLogRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ========== REPOSITORY BINDINGS ==========

        // Bind repositories
        $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AccountTypeRepositoryInterface::class, AccountTypeRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);

        // ========== INFRASTRUCTURE SERVICES ==========

        // Bind AuditLogRepository
        $this->app->bind(AuditLogRepository::class, function ($app) {
            return new AuditLogRepository();
        });

        // Bind TokenService
        $this->app->bind(TokenService::class, function ($app) {
            return new TokenService();
        });

        // Bind TwoFactorService
        $this->app->bind(TwoFactorService::class, function ($app) {
            return new TwoFactorService();
        });

        // Bind AuthenticationService
        $this->app->bind(AuthenticationService::class, function ($app) {
            return new AuthenticationService(
                $app->make(UserRepositoryInterface::class),
                $app->make(TokenService::class)
            );
        });

        // ========== APPLICATION SERVICES ==========

        // Bind AuditService
        $this->app->bind(AuditService::class, function ($app) {
            return new AuditService(
                $app->make(AuditLogRepository::class)
            );
        });

        // Bind TransferService (Domain Service)
        $this->app->bind(TransferService::class, function ($app) {
            return new TransferService(
                $app->make(AccountRepositoryInterface::class),
                $app->make(TransactionRepositoryInterface::class)
            );
        });

        // Bind TransactionService
        $this->app->bind(TransactionService::class, function ($app) {
            return new TransactionService(
                $app->make(AccountRepositoryInterface::class),
                $app->make(TransactionRepositoryInterface::class),
                $app->make(TransferService::class),
                $app->make(AuditService::class)
            );
        });

        // Bind AccountService
        $this->app->bind(AccountService::class, function ($app) {
            return new AccountService(
                $app->make(AccountRepositoryInterface::class),
                $app->make(UserRepositoryInterface::class),
                $app->make(AccountTypeRepositoryInterface::class),
                $app->make(TransactionService::class),
                $app->make(AuditService::class)
            );
        });

        // Bind AuthService
        $this->app->bind(AuthService::class, function ($app) {
            return new AuthService(
                $app->make(UserRepositoryInterface::class),
                $app->make(AuthenticationService::class),
                $app->make(TokenService::class),
                $app->make(TwoFactorService::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
