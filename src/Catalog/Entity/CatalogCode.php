<?php

declare(strict_types=1);

namespace App\Catalog\Entity;

/**
 * Identifiant lisible et stable d'un élément du référentiel (ex. "argon", "argon_federation").
 */
final class CatalogCode
{
    public const string PATTERN = '/^[a-z0-9]+(?:_[a-z0-9]+)*$/';
    public const int MAX_LENGTH = 64;

    private function __construct()
    {
    }

    public static function normalize(string $code): string
    {
        $normalized = mb_strtolower(trim($code));
        if ('' === $normalized || mb_strlen($normalized) > self::MAX_LENGTH || 1 !== preg_match(self::PATTERN, $normalized)) {
            throw new \InvalidArgumentException(\sprintf('"%s" n\'est pas un code valide : minuscules, chiffres et "_" uniquement, %d caractères maximum.', $code, self::MAX_LENGTH));
        }

        return $normalized;
    }

    public static function normalizeName(string $name): string
    {
        $normalized = trim($name);
        if ('' === $normalized) {
            throw new \InvalidArgumentException('Le nom ne peut pas être vide.');
        }

        return $normalized;
    }
}
