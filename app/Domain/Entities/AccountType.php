<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Money;
use Ramsey\Uuid\UuidInterface;
use DateTimeImmutable;

class AccountType
{
    private UuidInterface $id;
    private UuidInterface $tenantId;
    private string $code;
    private string $name;
    private string $description;
    private Money $minBalance;
    private Money $maxBalance;
    private string $interestRate;
    private bool $isActive;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        UuidInterface $id,
        UuidInterface $tenantId,
        string $code,
        string $name,
        Money $minBalance,
        Money $maxBalance,
        string $interestRate = '0',
        string $description = ''
    ) {
        $this->id = $id;
        $this->tenantId = $tenantId;
        $this->code = $code;
        $this->name = $name;
        $this->description = $description;
        $this->minBalance = $minBalance;
        $this->maxBalance = $maxBalance;
        $this->interestRate = $interestRate;
        $this->isActive = true;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();

        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->code)) {
            throw new \DomainException('Account type code is required');
        }

        if (empty($this->name)) {
            throw new \DomainException('Account type name is required');
        }

        if ($this->minBalance->isGreaterThan($this->maxBalance)) {
            throw new \DomainException('Minimum balance cannot be greater than maximum balance');
        }

        if ($this->interestRate < '0') {
            throw new \DomainException('Interest rate cannot be negative');
        }
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTenantId(): UuidInterface
    {
        return $this->tenantId;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getMinBalance(): Money
    {
        return $this->minBalance;
    }

    public function getMaxBalance(): Money
    {
        return $this->maxBalance;
    }

    public function getInterestRate(): string
    {
        return $this->interestRate;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDetails(
        string $name,
        string $description,
        Money $minBalance,
        Money $maxBalance,
        string $interestRate
    ): void {
        $this->name = $name;
        $this->description = $description;
        $this->minBalance = $minBalance;
        $this->maxBalance = $maxBalance;
        $this->interestRate = $interestRate;
        $this->updatedAt = new DateTimeImmutable();

        $this->validate();
    }
}
