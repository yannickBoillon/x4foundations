<?php

declare(strict_types=1);

namespace App\Catalog\Api\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * PATCH /api/races/{id} : seul le nom est modifiable, le code est immuable.
 */
final class RenameRaceInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $name = '';
}
