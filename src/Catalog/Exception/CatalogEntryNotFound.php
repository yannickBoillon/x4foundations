<?php

declare(strict_types=1);

namespace App\Catalog\Exception;

final class CatalogEntryNotFound extends \DomainException
{
    public static function withId(string $type, string $id): self
    {
        return new self(\sprintf('%s "%s" introuvable.', $type, $id));
    }
}
