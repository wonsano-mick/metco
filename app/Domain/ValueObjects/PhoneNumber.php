<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;

class PhoneNumber
{
    private string $rawNumber;
    private string $formattedNumber;
    private string $countryCode;
    private string $nationalNumber;
    private bool $isValid;

    public function __construct(string $phoneNumber, string $defaultRegion = 'US')
    {
        $this->validate($phoneNumber, $defaultRegion);
        $this->rawNumber = trim($phoneNumber);
        $this->parsePhoneNumber($defaultRegion);
    }

    private function validate(string $phoneNumber, string $defaultRegion): void
    {
        if (empty($phoneNumber)) {
            throw new InvalidArgumentException('Phone number cannot be empty');
        }

        // Basic pattern validation
        $pattern = '/^[+\d][\d\s\-\(\)\.]{7,}$/';
        if (!preg_match($pattern, $phoneNumber)) {
            throw new InvalidArgumentException('Invalid phone number format');
        }
    }

    private function parsePhoneNumber(string $defaultRegion): void
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $parsedNumber = $phoneUtil->parse($this->rawNumber, $defaultRegion);

            if ($phoneUtil->isValidNumber($parsedNumber)) {
                $this->isValid = true;
                $this->countryCode = $parsedNumber->getCountryCode();
                $this->nationalNumber = $parsedNumber->getNationalNumber();
                $this->formattedNumber = $phoneUtil->format(
                    $parsedNumber,
                    PhoneNumberFormat::E164
                );
            } else {
                $this->isValid = false;
                // Fallback: clean and store as-is
                $this->formattedNumber = $this->cleanNumber($this->rawNumber);
                $this->countryCode = $this->extractCountryCode($this->formattedNumber);
                $this->nationalNumber = $this->extractNationalNumber($this->formattedNumber);
            }
        } catch (NumberParseException $e) {
            $this->isValid = false;
            $this->formattedNumber = $this->cleanNumber($this->rawNumber);
            $this->countryCode = $this->extractCountryCode($this->formattedNumber);
            $this->nationalNumber = $this->extractNationalNumber($this->formattedNumber);
        }
    }

    private function cleanNumber(string $number): string
    {
        // Remove all non-numeric characters except leading +
        $cleaned = preg_replace('/[^\d+]/', '', $number);

        // If starts with 00, replace with +
        if (str_starts_with($cleaned, '00')) {
            $cleaned = '+' . substr($cleaned, 2);
        }

        // Add + if international number without it
        if (strlen($cleaned) > 10 && !str_starts_with($cleaned, '+')) {
            $cleaned = '+' . $cleaned;
        }

        return $cleaned;
    }

    private function extractCountryCode(string $number): string
    {
        if (str_starts_with($number, '+')) {
            $withoutPlus = substr($number, 1);

            // Common country codes
            $countryCodes = [
                '1' => 'US/CA',    // North America
                '44' => 'GB',      // UK
                '61' => 'AU',      // Australia
                '64' => 'NZ',      // New Zealand
                '81' => 'JP',      // Japan
                '86' => 'CN',      // China
                '91' => 'IN',      // India
            ];

            foreach ($countryCodes as $code => $country) {
                if (str_starts_with($withoutPlus, $code)) {
                    return $code;
                }
            }

            // Default: extract first 1-3 digits
            return substr($withoutPlus, 0, min(3, strlen($withoutPlus)));
        }

        return '1'; // Default to US/Canada
    }

    private function extractNationalNumber(string $number): string
    {
        if (str_starts_with($number, '+')) {
            $withoutPlus = substr($number, 1);
            $countryCodeLength = strlen($this->countryCode);
            return substr($withoutPlus, $countryCodeLength);
        }

        return $number;
    }

    public function getValue(): string
    {
        return $this->formattedNumber;
    }

    public function getFormatted(string $format = 'E164'): string
    {
        if (!$this->isValid) {
            return $this->formattedNumber;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();
        $parsedNumber = $phoneUtil->parse($this->formattedNumber, null);

        switch ($format) {
            case 'INTERNATIONAL':
                return $phoneUtil->format($parsedNumber, PhoneNumberFormat::INTERNATIONAL);
            case 'NATIONAL':
                return $phoneUtil->format($parsedNumber, PhoneNumberFormat::NATIONAL);
            case 'RFC3966':
                return $phoneUtil->format($parsedNumber, PhoneNumberFormat::RFC3966);
            default:
                return $this->formattedNumber;
        }
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getNationalNumber(): string
    {
        return $this->nationalNumber;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function equals(PhoneNumber $other): bool
    {
        return $this->formattedNumber === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->formattedNumber;
    }

    public function mask(): string
    {
        $number = $this->formattedNumber;

        if (strlen($number) <= 4) {
            return str_repeat('*', strlen($number));
        }

        $visibleDigits = 4;
        $maskedPart = str_repeat('*', strlen($number) - $visibleDigits);
        $visiblePart = substr($number, -$visibleDigits);

        return $maskedPart . $visiblePart;
    }

    public function isMobile(): bool
    {
        if (!$this->isValid) {
            return false;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();
        $parsedNumber = $phoneUtil->parse($this->formattedNumber, null);

        return $phoneUtil->getNumberType($parsedNumber) ===
            \libphonenumber\PhoneNumberType::MOBILE;
    }

    public static function createFromCountryCode(
        string $countryCode,
        string $nationalNumber
    ): self {
        $fullNumber = '+' . $countryCode . $nationalNumber;
        return new self($fullNumber);
    }
}
