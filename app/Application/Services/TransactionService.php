<?php

namespace App\Application\Services;

use App\Domain\Entities\Account;
use App\Domain\ValueObjects\Money;
use App\Domain\Repositories\AccountRepositoryInterface;
use App\Domain\Repositories\TransactionRepositoryInterface;
use App\Domain\Services\TransferService as DomainTransferService;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    private AccountRepositoryInterface $accountRepository;
    private TransactionRepositoryInterface $transactionRepository;
    private DomainTransferService $transferService;
    private AuditService $auditService;

    public function __construct(
        AccountRepositoryInterface $accountRepository,
        TransactionRepositoryInterface $transactionRepository,
        DomainTransferService $transferService,
        AuditService $auditService
    ) {
        $this->accountRepository = $accountRepository;
        $this->transactionRepository = $transactionRepository;
        $this->transferService = $transferService;
        $this->auditService = $auditService;
    }

    public function recordDeposit(
        string $accountId,
        Money $amount,
        string $description,
        string $initiatedBy
    ): string {
        return DB::transaction(function () use ($accountId, $amount, $description, $initiatedBy) {
            $account = $this->accountRepository->findAndLock(Uuid::fromString($accountId));

            if (!$account) {
                throw new \DomainException('Account not found');
            }

            if (!$account->isActive()) {
                throw new \DomainException('Account is not active');
            }

            // Create transaction - FIX: Use Uuid for initiatedBy
            $transaction = $this->transactionRepository->createDeposit(
                $amount,
                $description,
                $initiatedBy, // This is already a string
                $account->getTenantId()
            );

            // Perform deposit
            $account->deposit($amount);

            // Save changes
            $this->accountRepository->save($account);
            $this->transactionRepository->save($transaction);

            // Create ledger entry
            $this->transactionRepository->createLedgerEntry(
                $transaction->getId(),
                $account->getId(),
                'credit',
                $amount,
                $account->getCurrentBalance()
            );

            // Complete transaction
            $transaction->complete();
            $this->transactionRepository->save($transaction);

            // Audit
            $this->auditService->logDeposit(
                $initiatedBy,
                $accountId,
                $amount,
                $transaction->getId()->toString()
            );

            return $transaction->getId()->toString();
        });
    }

    public function recordWithdrawal(
        string $accountId,
        Money $amount,
        string $description,
        string $initiatedBy
    ): string {
        return DB::transaction(function () use ($accountId, $amount, $description, $initiatedBy) {
            $account = $this->accountRepository->findAndLock(Uuid::fromString($accountId));

            if (!$account) {
                throw new \DomainException('Account not found');
            }

            if (!$account->isActive()) {
                throw new \DomainException('Account is not active');
            }

            // Validate sufficient funds
            if ($account->getAvailableBalance()->isLessThan($amount)) {
                throw new \DomainException('Insufficient funds');
            }

            // Create transaction
            $transaction = $this->transactionRepository->createWithdrawal(
                $amount,
                $description,
                $initiatedBy,
                $account->getTenantId()
            );

            // Perform withdrawal
            $account->withdraw($amount);

            // Save changes
            $this->accountRepository->save($account);
            $this->transactionRepository->save($transaction);

            // Create ledger entry
            $this->transactionRepository->createLedgerEntry(
                $transaction->getId(),
                $account->getId(),
                'debit',
                $amount,
                $account->getCurrentBalance()
            );

            // Complete transaction
            $transaction->complete();
            $this->transactionRepository->save($transaction);

            // Audit
            $this->auditService->logWithdrawal(
                $initiatedBy,
                $accountId,
                $amount,
                $transaction->getId()->toString()
            );

            return $transaction->getId()->toString();
        });
    }

    public function transferFunds(
        string $fromAccountId,
        string $toAccountNumber,
        Money $amount,
        string $description,
        string $initiatedBy
    ): string {
        $fromAccount = $this->accountRepository->findById(Uuid::fromString($fromAccountId));

        if (!$fromAccount) {
            throw new \DomainException('Source account not found');
        }

        $toAccount = $this->accountRepository->findByAccountNumber(
            new \App\Domain\ValueObjects\AccountNumber($toAccountNumber)
        );

        if (!$toAccount) {
            throw new \DomainException('Destination account not found');
        }

        return $this->transferService->transferFunds(
            $fromAccount->getId(),
            $toAccount->getId(),
            $amount,
            $description,
            Uuid::fromString($initiatedBy) // Convert string to UuidInterface
        );
    }
}
