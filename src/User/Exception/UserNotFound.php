<?php

declare(strict_types=1);

namespace App\User\Exception;

final class UserNotFound extends \DomainException
{
    public static function withId(string $id): self
    {
        return new self(\sprintf('Utilisateur "%s" introuvable.', $id));
    }
}
