<?php

declare(strict_types=1);

namespace App\User\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\User\Api\Input\AssignRoleInput;
use App\User\Api\Resource\UserResource;
use App\User\Entity\Role;
use App\User\Repository\UserRepository;

/**
 * @implements ProcessorInterface<AssignRoleInput, UserResource>
 */
final readonly class AssignRoleProcessor implements ProcessorInterface
{
    public function __construct(private UserRepository $users)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserResource
    {
        \assert($data instanceof AssignRoleInput);

        $user = $this->users->get((string) $uriVariables['id']);
        $user->assignRole(Role::from($data->role));
        $this->users->save($user);

        return UserResource::fromEntity($user);
    }
}
