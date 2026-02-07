<?php

namespace App\Domain\Services;

use App\Domain\Entities\Account;
use App\Domain\ValueObjects\Money;
use App\Domain\Repositories\AccountRepositoryInterface;
use App\Domain\Repositories\TransactionRepositoryInterface;
use App\Domain\Events\FundsTransferred;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class TransferService
{
    private AccountRepositoryInterface $accountRepository;
    private TransactionRepositoryInterface $transactionRepository;

    public function __construct(
        AccountRepositoryInterface $accountRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->accountRepository = $accountRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function transferFunds(
        UuidInterface $fromAccountId,
        UuidInterface $toAccountId,
        Money $amount,
        string $description,
        UuidInterface $initiatedBy
    ): string {
        return DB::transaction(function () use ($fromAccountId, $toAccountId, $amount, $description, $initiatedBy) {
            // Lock both accounts for update
            $fromAccount = $this->accountRepository->findAndLock($fromAccountId);
            $toAccount = $this->accountRepository->findAndLock($toAccountId);

            if (!$fromAccount || !$toAccount) {
                throw new \DomainException('One or both accounts not found');
            }

            // Validate transfer
            $this->validateTransfer($fromAccount, $toAccount, $amount);

            // Create transaction
            $transaction = $this->transactionRepository->createTransfer(
                $amount,
                $description,
                $initiatedBy->toString(), // Convert UuidInterface to string
                $fromAccount->getTenantId()
            );

            // Perform transfer
            $fromAccount->withdraw($amount);
            $toAccount->deposit($amount);

            // Save changes
            $this->accountRepository->save($fromAccount);
            $this->accountRepository->save($toAccount);

            // Create ledger entries
            $this->transactionRepository->createLedgerEntry(
                $transaction->getId(),
                $fromAccountId,
                'debit',
                $amount,
                $fromAccount->getCurrentBalance()
            );

            $this->transactionRepository->createLedgerEntry(
                $transaction->getId(),
                $toAccountId,
                'credit',
                $amount,
                $toAccount->getCurrentBalance()
            );

            // Complete and save transaction
            $transaction->complete();
            $this->transactionRepository->save($transaction);

            // Dispatch event
            Event::dispatch(new FundsTransferred(
                $transaction->getId(),
                $fromAccountId,
                $toAccountId,
                $amount,
                $description
            ));

            return $transaction->getId()->toString();
        });
    }

    private function validateTransfer(Account $fromAccount, Account $toAccount, Money $amount): void
    {
        if (!$fromAccount->isActive()) {
            throw new \DomainException('Source account is not active');
        }

        if (!$toAccount->isActive()) {
            throw new \DomainException('Destination account is not active');
        }

        if ($fromAccount->getCurrency() !== $toAccount->getCurrency()) {
            throw new \DomainException('Currency mismatch between accounts');
        }

        if ($fromAccount->getId()->equals($toAccount->getId())) {
            throw new \DomainException('Cannot transfer to same account');
        }

        // Create a zero money object for comparison
        $zero = new Money('0', $amount->getCurrency());

        if ($amount->isLessThanOrEqualTo($zero)) {
            throw new \DomainException('Transfer amount must be positive');
        }

        if ($fromAccount->getAvailableBalance()->isLessThan($amount)) {
            throw new \DomainException('Insufficient funds');
        }
    }
}
