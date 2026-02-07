<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class AccountNumber
{
    private string $number;
    private string $countryCode;
    private string $checkDigits;
    private string $basicBankAccountNumber;

    public function __construct(string $number)
    {
        $this->validate($number);
        $this->parse($number);
    }

    public static function generate(string $countryCode = 'US'): self
    {
        // Generate IBAN-compatible account number
        $bankCode = str_pad((string) mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $branchCode = str_pad((string) mt_rand(100, 999), 3, '0', STR_PAD_LEFT);
        $accountNumber = str_pad((string) mt_rand(1000000, 9999999), 10, '0', STR_PAD_LEFT);
        $nationalCheck = str_pad((string) mt_rand(0, 99), 2, '0', STR_PAD_LEFT);

        $bban = $bankCode . $branchCode . $nationalCheck . $accountNumber;
        $checkDigits = self::calculateCheckDigits($countryCode, $bban);

        return new self($countryCode . $checkDigits . $bban);
    }

    private function validate(string $number): void
    {
        // Remove spaces and convert to uppercase
        $number = strtoupper(preg_replace('/\s+/', '', $number));

        if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', $number)) {
            throw new InvalidArgumentException('Invalid account number format');
        }

        if (!$this->validateCheckDigits($number)) {
            throw new InvalidArgumentException('Invalid check digits');
        }
    }

    private function parse(string $number): void
    {
        $number = strtoupper(preg_replace('/\s+/', '', $number));

        $this->countryCode = substr($number, 0, 2);
        $this->checkDigits = substr($number, 2, 2);
        $this->basicBankAccountNumber = substr($number, 4);
        $this->number = $number;
    }

    private function validateCheckDigits(string $iban): bool
    {
        $countryCode = substr($iban, 0, 2);
        $checkDigits = substr($iban, 2, 2);
        $bban = substr($iban, 4);

        $rearranged = $bban . $countryCode . $checkDigits;
        $numeric = '';

        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            if (ctype_digit($char)) {
                $numeric .= $char;
            } else {
                $numeric .= (ord($char) - 55);
            }
        }

        return bcmod($numeric, '97') === '1';
    }

    private static function calculateCheckDigits(string $countryCode, string $bban): string
    {
        $rearranged = $bban . $countryCode . '00';
        $numeric = '';

        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            if (ctype_digit($char)) {
                $numeric .= $char;
            } else {
                $numeric .= (ord($char) - 55);
            }
        }

        $remainder = bcmod($numeric, '97');
        $checkDigits = 98 - (int) $remainder;

        return str_pad((string) $checkDigits, 2, '0', STR_PAD_LEFT);
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getFormatted(): string
    {
        // Format as groups of 4 characters
        return implode(' ', str_split($this->number, 4));
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getBankCode(): string
    {
        return substr($this->basicBankAccountNumber, 0, 4);
    }

    public function getBranchCode(): string
    {
        return substr($this->basicBankAccountNumber, 4, 3);
    }

    public function getAccountNumber(): string
    {
        return substr($this->basicBankAccountNumber, 9);
    }

    public function __toString(): string
    {
        return $this->getFormatted();
    }
}
