<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class Address
{
    private string $street;
    private string $city;
    private string $state;
    private string $postalCode;
    private string $country;
    private ?string $street2;

    public function __construct(
        string $street,
        string $city,
        string $state,
        string $postalCode,
        string $country = 'US',
        ?string $street2 = null
    ) {
        $this->validate($street, $city, $state, $postalCode, $country);

        $this->street = trim($street);
        $this->city = trim($city);
        $this->state = strtoupper(trim($state));
        $this->postalCode = trim($postalCode);
        $this->country = strtoupper(trim($country));
        $this->street2 = $street2 ? trim($street2) : null;
    }

    private function validate(
        string $street,
        string $city,
        string $state,
        string $postalCode,
        string $country
    ): void {
        if (empty($street)) {
            throw new InvalidArgumentException('Street address is required');
        }

        if (empty($city)) {
            throw new InvalidArgumentException('City is required');
        }

        if (empty($state)) {
            throw new InvalidArgumentException('State is required');
        }

        if (empty($postalCode)) {
            throw new InvalidArgumentException('Postal code is required');
        }

        if (empty($country)) {
            throw new InvalidArgumentException('Country is required');
        }

        if (!preg_match('/^[A-Z]{2}$/', $country)) {
            throw new InvalidArgumentException('Country must be a 2-letter ISO code');
        }

        if (!preg_match('/^\d{5}(-\d{4})?$/', $postalCode) && $country === 'US') {
            throw new InvalidArgumentException('Invalid US postal code format');
        }
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getStreet2(): ?string
    {
        return $this->street2;
    }

    public function getFullAddress(): string
    {
        $address = $this->street;

        if ($this->street2) {
            $address .= ', ' . $this->street2;
        }

        $address .= ', ' . $this->city . ', ' . $this->state . ' ' . $this->postalCode;

        if ($this->country !== 'US') {
            $address .= ', ' . $this->country;
        }

        return $address;
    }

    public function equals(Address $other): bool
    {
        return $this->getFullAddress() === $other->getFullAddress();
    }

    public function __toString(): string
    {
        return $this->getFullAddress();
    }

    public function isDomestic(): bool
    {
        return $this->country === 'US';
    }

    public function mask(): string
    {
        // Mask street address for privacy
        $maskedStreet = preg_replace('/\d/', '*', $this->street);
        return $maskedStreet . ', ' . $this->city . ', ' . $this->state;
    }
}
