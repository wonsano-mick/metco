<?php

namespace App\Application\Services;

use App\Domain\ValueObjects\Money;
use App\Infrastructure\Repositories\AuditLogRepository;
use Illuminate\Support\Facades\Request;

class AuditService
{
    private AuditLogRepository $auditLogRepository;

    public function __construct(AuditLogRepository $auditLogRepository)
    {
        $this->auditLogRepository = $auditLogRepository;
    }

    public function logAccountCreated(
        string $userId,
        string $accountId,
        string $accountNumber,
        string $accountType
    ): void {
        $this->auditLogRepository->log([
            'user_id' => $userId,
            'action' => 'account_created',
            'entity_type' => 'account',
            'entity_id' => $accountId,
            'new_values' => [
                'account_number' => $accountNumber,
                'account_type' => $accountType,
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public function logDeposit(
        string $userId,
        string $accountId,
        Money $amount,
        string $transactionId
    ): void {
        $this->auditLogRepository->log([
            'user_id' => $userId,
            'action' => 'deposit',
            'entity_type' => 'transaction',
            'entity_id' => $transactionId,
            'new_values' => [
                'account_id' => $accountId,
                'amount' => $amount->getAmount(),
                'currency' => $amount->getCurrency(),
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public function logWithdrawal(
        string $userId,
        string $accountId,
        Money $amount,
        string $transactionId
    ): void {
        $this->auditLogRepository->log([
            'user_id' => $userId,
            'action' => 'withdrawal',
            'entity_type' => 'transaction',
            'entity_id' => $transactionId,
            'new_values' => [
                'account_id' => $accountId,
                'amount' => $amount->getAmount(),
                'currency' => $amount->getCurrency(),
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public function logTransfer(
        string $userId,
        string $fromAccountId,
        string $toAccountId,
        Money $amount,
        string $transactionId
    ): void {
        $this->auditLogRepository->log([
            'user_id' => $userId,
            'action' => 'transfer',
            'entity_type' => 'transaction',
            'entity_id' => $transactionId,
            'new_values' => [
                'from_account_id' => $fromAccountId,
                'to_account_id' => $toAccountId,
                'amount' => $amount->getAmount(),
                'currency' => $amount->getCurrency(),
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public function logFailedTransfer(
        string $userId,
        string $fromAccountId,
        string $toAccountId,
        Money $amount,
        string $error
    ): void {
        $this->auditLogRepository->log([
            'user_id' => $userId,
            'action' => 'transfer_failed',
            'entity_type' => 'transaction',
            'new_values' => [
                'from_account_id' => $fromAccountId,
                'to_account_id' => $toAccountId,
                'amount' => $amount->getAmount(),
                'currency' => $amount->getCurrency(),
                'error' => $error,
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public function logAccountFrozen(
        string $adminUserId,
        string $accountId,
        string $accountNumber,
        string $reason
    ): void {
        $this->auditLogRepository->log([
            'user_id' => $adminUserId,
            'action' => 'account_frozen',
            'entity_type' => 'account',
            'entity_id' => $accountId,
            'new_values' => [
                'account_number' => $accountNumber,
                'status' => 'frozen',
                'reason' => $reason,
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public function logLogin(
        string $userId,
        bool $success,
        ?string $failureReason = null
    ): void {
        $this->auditLogRepository->log([
            'user_id' => $userId,
            'action' => $success ? 'login_success' : 'login_failed',
            'entity_type' => 'user',
            'entity_id' => $userId,
            'new_values' => $success ? null : ['failure_reason' => $failureReason],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
