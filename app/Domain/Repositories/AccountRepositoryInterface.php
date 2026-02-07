<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Account;
use App\Domain\ValueObjects\AccountNumber;
use Ramsey\Uuid\UuidInterface;
use DateTimeInterface;

interface AccountRepositoryInterface
{
    public function findById(UuidInterface $id): ?Account;
    public function findByAccountNumber(AccountNumber $accountNumber): ?Account;
    public function findAndLock(UuidInterface $id): ?Account;
    public function save(Account $account): void;
    public function delete(UuidInterface $id): void;
    public function getUserAccounts(UuidInterface $userId): array;
    public function getTransactions(
        UuidInterface $accountId,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        int $page = 1,
        int $perPage = 50
    ): array;
    public function getTransactionSummary(
        UuidInterface $accountId,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate
    ): array;
}
