<?php

declare(strict_types=1);

namespace App\User\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\User\Entity\Role;
use App\User\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @implements ProcessorInterface<mixed, null>
 */
final readonly class RevokeRoleProcessor implements ProcessorInterface
{
    public function __construct(private UserRepository $users)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        $role = Role::tryFrom(strtoupper((string) $uriVariables['role']))
            ?? throw new UnprocessableEntityHttpException(\sprintf('Rôle inconnu. Rôles possibles : %s', implode(', ', Role::values())));

        $user = $this->users->get((string) $uriVariables['id']);
        $user->revokeRole($role);
        $this->users->save($user);

        return null;
    }
}
