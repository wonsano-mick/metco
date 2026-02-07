<?php

namespace App\Application\DTOs;

use App\Domain\ValueObjects\Money;

class CreateAccountDTO
{
    public string $userId;
    public string $accountType;
    public Money $initialDeposit;
    public string $currency;

    public function __construct(
        string $userId,
        string $accountType,
        Money $initialDeposit,
        string $currency = 'USD'
    ) {
        $this->userId = $userId;
        $this->accountType = $accountType;
        $this->initialDeposit = $initialDeposit;
        $this->currency = $currency;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['userId'],
            $data['accountType'],
            new Money($data['initialDeposit'], $data['currency'] ?? 'USD'),
            $data['currency'] ?? 'USD'
        );
    }
}
