<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Money;
use Ramsey\Uuid\UuidInterface;
use DateTimeImmutable;

class Transaction
{
    private UuidInterface $id;
    private UuidInterface $tenantId;
    private string $type;
    private Money $amount;
    private string $description;
    private UuidInterface $initiatedBy;
    private string $reference;
    private string $status;
    private array $metadata;
    private DateTimeImmutable $initiatedAt;
    private ?DateTimeImmutable $completedAt;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        UuidInterface $id,
        UuidInterface $tenantId,
        string $type,
        Money $amount,
        string $description,
        UuidInterface $initiatedBy,
        ?string $reference = null,
        string $status = 'pending',
        array $metadata = [],
        ?DateTimeImmutable $initiatedAt = null,
        ?DateTimeImmutable $completedAt = null
    ) {
        $this->id = $id;
        $this->tenantId = $tenantId;
        $this->type = $type;
        $this->amount = $amount;
        $this->description = $description;
        $this->initiatedBy = $initiatedBy;
        $this->reference = $reference ?? $this->generateReference();
        $this->status = $status;
        $this->metadata = $metadata;
        $this->initiatedAt = $initiatedAt ?? new DateTimeImmutable();
        $this->completedAt = $completedAt;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();

        $this->validate();
    }

    private function validate(): void
    {
        $validTypes = ['deposit', 'withdrawal', 'transfer', 'fee', 'interest'];
        if (!in_array($this->type, $validTypes)) {
            throw new \DomainException('Invalid transaction type');
        }

        $validStatuses = ['pending', 'completed', 'failed', 'reversed'];
        if (!in_array($this->status, $validStatuses)) {
            throw new \DomainException('Invalid transaction status');
        }

        if ($this->amount->isLessThanOrEqualTo(new Money('0'))) {
            throw new \DomainException('Transaction amount must be positive');
        }
    }

    private function generateReference(): string
    {
        return 'TXN' . time() . mt_rand(1000, 9999);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTenantId(): UuidInterface
    {
        return $this->tenantId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getInitiatedBy(): UuidInterface
    {
        return $this->initiatedBy;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getInitiatedAt(): DateTimeImmutable
    {
        return $this->initiatedAt;
    }

    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function complete(): void
    {
        $this->status = 'completed';
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function fail(string $reason = null): void
    {
        $this->status = 'failed';
        if ($reason) {
            $this->metadata['failure_reason'] = $reason;
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reverse(): void
    {
        $this->status = 'reversed';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isReversed(): bool
    {
        return $this->status === 'reversed';
    }

    public function addMetadata(string $key, $value): void
    {
        $this->metadata[$key] = $value;
        $this->updatedAt = new DateTimeImmutable();
    }
}
