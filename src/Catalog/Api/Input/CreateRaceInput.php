<?php

declare(strict_types=1);

namespace App\Catalog\Api\Input;

use App\Catalog\Entity\CatalogCode;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * POST /api/races
 */
final class CreateRaceInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: CatalogCode::MAX_LENGTH)]
    #[Assert\Regex(pattern: CatalogCode::PATTERN, message: 'Minuscules, chiffres et "_" uniquement (ex. "argon_federation").')]
    public string $code = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $name = '';
}
