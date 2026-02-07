<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class ValueObjectFactory
{
    public static function createEmail(string $email): Email
    {
        return new Email($email);
    }

    public static function createPhoneNumber(string $phoneNumber, string $defaultRegion = 'US'): PhoneNumber
    {
        return new PhoneNumber($phoneNumber, $defaultRegion);
    }

    public static function createAddress(
        string $street,
        string $city,
        string $state,
        string $postalCode,
        string $country = 'US',
        ?string $street2 = null
    ): Address {
        return new Address($street, $city, $state, $postalCode, $country, $street2);
    }

    public static function createMoney(string $amount, string $currency = 'USD'): Money
    {
        return new Money($amount, $currency);
    }

    public static function createAccountNumber(string $number): AccountNumber
    {
        return new AccountNumber($number);
    }

    /**
     * Create value objects from array data
     */
    public static function fromArray(array $data): array
    {
        $objects = [];

        if (isset($data['email'])) {
            $objects['email'] = self::createEmail($data['email']);
        }

        if (isset($data['phone'])) {
            $objects['phone'] = self::createPhoneNumber($data['phone']);
        }

        if (isset($data['address'])) {
            $addressData = $data['address'];
            $objects['address'] = self::createAddress(
                $addressData['street'] ?? '',
                $addressData['city'] ?? '',
                $addressData['state'] ?? '',
                $addressData['postal_code'] ?? '',
                $addressData['country'] ?? 'US',
                $addressData['street2'] ?? null
            );
        }

        if (isset($data['amount']) && isset($data['currency'])) {
            $objects['money'] = self::createMoney($data['amount'], $data['currency']);
        }

        return $objects;
    }
}
