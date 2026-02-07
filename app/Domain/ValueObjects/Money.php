<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class Money
{
    private string $amount;
    private string $currency;

    public function __construct(string $amount, string $currency = 'USD')
    {
        $this->validateAmount($amount);
        $this->validateCurrency($currency);

        $this->amount = bcadd($amount, '0', 4);
        $this->currency = strtoupper($currency);
    }

    public static function fromCents(int $cents, string $currency = 'USD'): self
    {
        $amount = bcdiv((string) $cents, '100', 2);
        return new self($amount, $currency);
    }

    public function isLessThanOrEqualTo(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return bccomp($this->amount, $other->amount, 4) <= 0;
    }

    public function add(Money $other): self
    {
        $this->assertSameCurrency($other);
        return new self(bcadd($this->amount, $other->amount, 4), $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->assertSameCurrency($other);
        return new self(bcsub($this->amount, $other->amount, 4), $this->currency);
    }

    public function multiply(string $multiplier): self
    {
        return new self(bcmul($this->amount, $multiplier, 4), $this->currency);
    }

    public function divide(string $divisor): self
    {
        if (bccomp($divisor, '0', 4) === 0) {
            throw new InvalidArgumentException('Division by zero');
        }

        return new self(bcdiv($this->amount, $divisor, 4), $this->currency);
    }

    public function isGreaterThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return bccomp($this->amount, $other->amount, 4) === 1;
    }

    public function isGreaterThanOrEqual(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return bccomp($this->amount, $other->amount, 4) >= 0;
    }

    public function isLessThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return bccomp($this->amount, $other->amount, 4) === -1;
    }

    public function isLessThanOrEqual(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return bccomp($this->amount, $other->amount, 4) <= 0;
    }

    public function isZero(): bool
    {
        return bccomp($this->amount, '0', 4) === 0;
    }

    public function isPositive(): bool
    {
        return bccomp($this->amount, '0', 4) === 1;
    }

    public function isNegative(): bool
    {
        return bccomp($this->amount, '0', 4) === -1;
    }

    public function equals(Money $other): bool
    {
        return $this->currency === $other->currency &&
            bccomp($this->amount, $other->amount, 4) === 0;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCents(): int
    {
        return (int) bcmul($this->amount, '100', 0);
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function format(): string
    {
        return number_format((float) $this->amount, 2) . ' ' . $this->currency;
    }

    private function validateAmount(string $amount): void
    {
        if (!preg_match('/^-?\d+(\.\d{1,4})?$/', $amount)) {
            throw new InvalidArgumentException('Invalid amount format');
        }

        if (bccomp($amount, '0', 4) < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    private function validateCurrency(string $currency): void
    {
        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException('Invalid currency code');
        }
    }

    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Currencies do not match');
        }
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
