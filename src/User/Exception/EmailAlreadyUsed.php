<?php

declare(strict_types=1);

namespace App\User\Exception;

final class EmailAlreadyUsed extends \DomainException
{
    public static function for(string $email): self
    {
        return new self(\sprintf('L\'email "%s" est déjà utilisé.', $email));
    }
}
