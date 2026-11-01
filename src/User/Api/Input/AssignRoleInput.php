<?php

declare(strict_types=1);

namespace App\User\Api\Input;

use App\User\Entity\Role;
use Symfony\Component\Validator\Constraints as Assert;

final class AssignRoleInput
{
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [Role::class, 'values'])]
    public string $role = '';
}
