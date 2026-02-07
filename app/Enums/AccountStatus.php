<?php

namespace App\Enums;

enum AccountStatus: string
{
    case ACTIVE = 'active';
    case FROZEN = 'frozen';
    case CLOSED = 'closed';
    case PENDING = 'pending';
    case DORMANT = 'dormant';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::FROZEN => 'Frozen',
            self::CLOSED => 'Closed',
            self::PENDING => 'Pending Activation',
            self::DORMANT => 'Dormant',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'green',
            self::FROZEN => 'yellow',
            self::CLOSED => 'red',
            self::PENDING => 'blue',
            self::DORMANT => 'gray',
            self::SUSPENDED => 'orange',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-green-100 text-green-800',
            self::FROZEN => 'bg-yellow-100 text-yellow-800',
            self::CLOSED => 'bg-red-100 text-red-800',
            self::PENDING => 'bg-blue-100 text-blue-800',
            self::DORMANT => 'bg-gray-100 text-gray-800',
            self::SUSPENDED => 'bg-orange-100 text-orange-800',
        };
    }
}
