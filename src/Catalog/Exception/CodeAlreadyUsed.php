<?php

declare(strict_types=1);

namespace App\Catalog\Exception;

final class CodeAlreadyUsed extends \DomainException
{
    public static function for(string $type, string $code): self
    {
        return new self(\sprintf('%s : le code "%s" est déjà utilisé.', $type, $code));
    }
}
