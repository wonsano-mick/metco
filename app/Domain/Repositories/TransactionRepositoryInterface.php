<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Transaction;
use App\Domain\ValueObjects\Money;
use Ramsey\Uuid\UuidInterface;

interface TransactionRepositoryInterface
{
    public function findById(UuidInterface $id): ?Transaction;
    public function save(Transaction $transaction): void;
    public function createDeposit(
        Money $amount,
        string $description,
        string $initiatedBy,
        UuidInterface $tenantId
    ): Transaction;
    public function createWithdrawal(
        Money $amount,
        string $description,
        string $initiatedBy,
        UuidInterface $tenantId
    ): Transaction;
    public function createTransfer(
        Money $amount,
        string $description,
        string $initiatedBy,
        UuidInterface $tenantId
    ): Transaction;
    public function createLedgerEntry(
        UuidInterface $transactionId,
        UuidInterface $accountId,
        string $entryType,
        Money $amount,
        Money $balanceAfter
    ): void;
}
