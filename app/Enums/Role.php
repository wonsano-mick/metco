<?php

namespace App\Enums;

enum Role: string
{
    case SUPER_ADMIN = 'super-admin';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case TELLER = 'teller';
    case ACCOUNTANT = 'accountant';
    case AUDITOR = 'auditor';
    case CUSTOMER = 'customer';
    case SUPERVISOR = 'supervisor';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::MANAGER => 'Branch Manager',
            self::TELLER => 'Teller',
            self::ACCOUNTANT => 'Accountant',
            self::AUDITOR => 'Auditor',
            self::CUSTOMER => 'Customer',
            self::SUPERVISOR => 'supervisor',
        };
    }

    // RENAME THIS METHOD - DON'T OVERRIDE cases()
    public static function allCases(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::MANAGER,
            self::TELLER,
            self::ACCOUNTANT,
            self::AUDITOR,
            self::CUSTOMER,
        ];
    }

    public static function getLabel(string $value): string
    {
        try {
            return self::from($value)->label();
        } catch (\ValueError $e) {
            return ucfirst(str_replace('_', ' ', $value));
        }
    }

    // You can also add this helper method to get all values
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
