<?php

declare(strict_types=1);

namespace App\User\Entity;

enum Role: string
{
    case User = 'ROLE_USER';
    case Pilot = 'ROLE_PILOT';
    case FleetCommander = 'ROLE_FLEET_COMMANDER';
    case Admin = 'ROLE_ADMIN';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
