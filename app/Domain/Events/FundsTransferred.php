<?php

namespace App\Domain\Events;

use App\Domain\ValueObjects\Money;
use Ramsey\Uuid\UuidInterface;

class FundsTransferred
{
    private UuidInterface $transactionId;
    private UuidInterface $fromAccountId;
    private UuidInterface $toAccountId;
    private Money $amount;
    private string $description;
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        UuidInterface $transactionId,
        UuidInterface $fromAccountId,
        UuidInterface $toAccountId,
        Money $amount,
        string $description
    ) {
        $this->transactionId = $transactionId;
        $this->fromAccountId = $fromAccountId;
        $this->toAccountId = $toAccountId;
        $this->amount = $amount;
        $this->description = $description;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getTransactionId(): UuidInterface
    {
        return $this->transactionId;
    }

    public function getFromAccountId(): UuidInterface
    {
        return $this->fromAccountId;
    }

    public function getToAccountId(): UuidInterface
    {
        return $this->toAccountId;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
