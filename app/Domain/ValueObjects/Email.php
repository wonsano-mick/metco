<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class Email
{
    private string $email;
    private string $localPart;
    private string $domain;

    public function __construct(string $email)
    {
        $this->validate($email);
        $this->email = strtolower(trim($email));
        $this->parseEmail();
    }

    private function validate(string $email): void
    {
        // Basic validation
        if (empty($email)) {
            throw new InvalidArgumentException('Email cannot be empty');
        }

        // Check length
        if (strlen($email) > 254) {
            throw new InvalidArgumentException('Email is too long');
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        // Check for disposable email domains (optional security)
        if ($this->isDisposableEmail($email)) {
            throw new InvalidArgumentException('Disposable email addresses are not allowed');
        }
    }

    private function parseEmail(): void
    {
        $parts = explode('@', $this->email, 2);
        $this->localPart = $parts[0];
        $this->domain = $parts[1];
    }

    private function isDisposableEmail(string $email): bool
    {
        $disposableDomains = [
            'tempmail.com',
            'temp-mail.org',
            'guerrillamail.com',
            'mailinator.com',
            'yopmail.com',
            'trashmail.com',
            '10minutemail.com',
            'dispostable.com',
            'fakeinbox.com'
        ];

        $domain = strtolower(explode('@', $email)[1] ?? '');
        return in_array($domain, $disposableDomains);
    }

    public function getValue(): string
    {
        return $this->email;
    }

    public function getLocalPart(): string
    {
        return $this->localPart;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function equals(Email $other): bool
    {
        return $this->email === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public function isGmail(): bool
    {
        return str_ends_with($this->domain, 'gmail.com');
    }

    public function isCorporate(): bool
    {
        $corporateDomains = [
            'outlook.com',
            'hotmail.com',
            'yahoo.com',
            'icloud.com',
            'aol.com',
            'protonmail.com'
        ];

        return !in_array($this->domain, $corporateDomains);
    }

    public function mask(): string
    {
        $localPart = $this->localPart;
        $length = strlen($localPart);

        if ($length <= 2) {
            return str_repeat('*', $length) . '@' . $this->domain;
        }

        $firstChar = $localPart[0];
        $lastChar = $localPart[$length - 1];
        $masked = $firstChar . str_repeat('*', $length - 2) . $lastChar;

        return $masked . '@' . $this->domain;
    }
}
