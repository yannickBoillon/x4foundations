<?php

declare(strict_types=1);

namespace App\Api;

/**
 * Lecture typée des variables d'URI reçues par les processors API Platform.
 */
final class UriVariable
{
    private function __construct()
    {
    }

    /**
     * Les identifiants UUID arrivent convertis en objet Uuid par API Platform, les autres en chaîne.
     *
     * @param array<string, mixed> $uriVariables
     */
    public static function string(array $uriVariables, string $name): string
    {
        $value = $uriVariables[$name] ?? null;
        if (!\is_string($value) && !$value instanceof \Stringable) {
            throw new \LogicException(\sprintf('Variable d\'URI "%s" absente ou invalide.', $name));
        }

        return (string) $value;
    }
}
