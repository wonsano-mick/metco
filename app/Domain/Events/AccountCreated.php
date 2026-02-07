<?php

namespace App\Domain\Events;

use App\Domain\ValueObjects\Money;
use Ramsey\Uuid\UuidInterface;

class AccountCreated
{
    private UuidInterface $accountId;
    private UuidInterface $userId;
    private string $accountNumber;
    private string $accountType;
    private Money $initialBalance;
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        UuidInterface $accountId,
        UuidInterface $userId,
        string $accountNumber,
        string $accountType,
        Money $initialBalance
    ) {
        $this->accountId = $accountId;
        $this->userId = $userId;
        $this->accountNumber = $accountNumber;
        $this->accountType = $accountType;
        $this->initialBalance = $initialBalance;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getAccountId(): UuidInterface
    {
        return $this->accountId;
    }

    public function getUserId(): UuidInterface
    {
        return $this->userId;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getAccountType(): string
    {
        return $this->accountType;
    }

    public function getInitialBalance(): Money
    {
        return $this->initialBalance;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
