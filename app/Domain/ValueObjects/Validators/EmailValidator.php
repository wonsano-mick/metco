<?php

namespace App\Domain\ValueObjects\Validators;

class EmailValidator
{
    private static array $disposableDomains = [
        'tempmail.com',
        'temp-mail.org',
        'guerrillamail.com',
        'mailinator.com',
        'yopmail.com',
        'trashmail.com',
        '10minutemail.com',
        'dispostable.com',
        'fakeinbox.com',
        'sharklasers.com',
        'guerrillamail.net',
        'grr.la',
        'guerrillamail.biz',
        'guerrillamail.de',
        'spam4.me'
    ]; 

    public static function isValid(string $email): bool
    {
        if (empty($email) || strlen($email) > 254) {
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }

    public static function isDisposable(string $email): bool
    {
        $domain = self::extractDomain($email);
        return in_array($domain, self::$disposableDomains);
    }

    public static function isCorporate(string $email): bool
    {
        $freeDomains = [
            'gmail.com',
            'yahoo.com',
            'hotmail.com',
            'outlook.com',
            'icloud.com',
            'aol.com',
            'protonmail.com',
            'zoho.com',
            'yandex.com',
            'mail.com'
        ];

        $domain = self::extractDomain($email);
        return !in_array($domain, $freeDomains);
    }

    public static function extractDomain(string $email): string
    {
        $parts = explode('@', $email, 2);
        return count($parts) === 2 ? strtolower($parts[1]) : '';
    }
}
