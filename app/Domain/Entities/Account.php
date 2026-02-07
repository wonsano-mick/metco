<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\AccountNumber;
use Ramsey\Uuid\UuidInterface;
use DateTimeImmutable;

class Account
{
    private UuidInterface $id;
    private UuidInterface $tenantId;
    private UuidInterface $userId;
    private AccountNumber $accountNumber;
    private UuidInterface $accountTypeId;
    private Money $currentBalance;
    private Money $availableBalance;
    private Money $ledgerBalance;
    private string $currency;
    private string $status;
    private DateTimeImmutable $openedAt;
    private ?DateTimeImmutable $closedAt;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        UuidInterface $id,
        UuidInterface $tenantId,
        UuidInterface $userId,
        AccountNumber $accountNumber,
        UuidInterface $accountTypeId,
        Money $initialBalance,
        string $currency = 'USD'
    ) {
        $this->id = $id;
        $this->tenantId = $tenantId;
        $this->userId = $userId;
        $this->accountNumber = $accountNumber;
        $this->accountTypeId = $accountTypeId;
        $this->currentBalance = $initialBalance;
        $this->availableBalance = $initialBalance;
        $this->ledgerBalance = $initialBalance;
        $this->currency = $currency;
        $this->status = 'active';
        $this->openedAt = new DateTimeImmutable();
        $this->closedAt = null;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTenantId(): UuidInterface
    {
        return $this->tenantId;
    }

    public function getUserId(): UuidInterface
    {
        return $this->userId;
    }

    public function getAccountNumber(): AccountNumber
    {
        return $this->accountNumber;
    }

    public function getAccountTypeId(): UuidInterface
    {
        return $this->accountTypeId;
    }

    public function getCurrentBalance(): Money
    {
        return $this->currentBalance;
    }

    public function getAvailableBalance(): Money
    {
        return $this->availableBalance;
    }

    public function getLedgerBalance(): Money
    {
        return $this->ledgerBalance;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getOpenedAt(): DateTimeImmutable
    {
        return $this->openedAt;
    }

    public function getClosedAt(): ?DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function deposit(Money $amount): void
    {
        $this->validateActive();
        $this->validateCurrency($amount);

        $this->currentBalance = $this->currentBalance->add($amount);
        $this->availableBalance = $this->availableBalance->add($amount);
        $this->ledgerBalance = $this->ledgerBalance->add($amount);

        $this->updatedAt = new DateTimeImmutable();
    }

    public function withdraw(Money $amount): void
    {
        $this->validateActive();
        $this->validateCurrency($amount);
        $this->validateSufficientFunds($amount);

        $this->currentBalance = $this->currentBalance->subtract($amount);
        $this->availableBalance = $this->availableBalance->subtract($amount);
        $this->ledgerBalance = $this->ledgerBalance->subtract($amount);

        $this->updatedAt = new DateTimeImmutable();
    }

    public function holdFunds(Money $amount): void
    {
        $this->validateActive();
        $this->validateCurrency($amount);

        if ($this->availableBalance->isLessThan($amount)) {
            throw new \DomainException('Insufficient available funds');
        }

        $this->availableBalance = $this->availableBalance->subtract($amount);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function releaseHold(Money $amount): void
    {
        $this->validateActive();
        $this->validateCurrency($amount);

        $this->availableBalance = $this->availableBalance->add($amount);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function close(): void
    {
        if ($this->status === 'closed') {
            throw new \DomainException('Account is already closed');
        }

        if (!$this->currentBalance->isZero()) {
            throw new \DomainException('Cannot close account with balance');
        }

        $this->status = 'closed';
        $this->closedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function freeze(): void
    {
        if ($this->status === 'closed') {
            throw new \DomainException('Cannot freeze closed account');
        }

        $this->status = 'frozen';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function unfreeze(): void
    {
        if ($this->status !== 'frozen') {
            throw new \DomainException('Account is not frozen');
        }

        $this->status = 'active';
        $this->updatedAt = new DateTimeImmutable();
    }

    private function validateActive(): void
    {
        if ($this->status !== 'active') {
            throw new \DomainException("Account is {$this->status}");
        }
    }

    private function validateCurrency(Money $amount): void
    {
        if ($this->currency !== $amount->getCurrency()) {
            throw new \DomainException('Currency mismatch');
        }
    }

    private function validateSufficientFunds(Money $amount): void
    {
        if ($this->availableBalance->isLessThan($amount)) {
            throw new \DomainException('Insufficient funds');
        }
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
