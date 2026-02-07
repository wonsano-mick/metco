<?php

namespace App\Application\DTOs;

use App\Domain\ValueObjects\Money;

class TransferDTO
{
    public string $fromAccountId;
    public string $toAccountNumber;
    public Money $amount;
    public string $description;
    public string $initiatedBy;
    public ?string $reference = null;

    public function __construct(
        string $fromAccountId,
        string $toAccountNumber,
        Money $amount,
        string $description,
        string $initiatedBy,
        ?string $reference = null
    ) {
        $this->fromAccountId = $fromAccountId;
        $this->toAccountNumber = $toAccountNumber;
        $this->amount = $amount;
        $this->description = $description;
        $this->initiatedBy = $initiatedBy;
        $this->reference = $reference;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['fromAccountId'],
            $data['toAccountNumber'],
            new Money($data['amount'], $data['currency'] ?? 'USD'),
            $data['description'],
            $data['initiatedBy'],
            $data['reference'] ?? null
        );
    }
}
