<?php

declare(strict_types=1);

namespace App\User\Api\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\User\Api\Resource\UserResource;
use App\User\Repository\UserRepository;
use Symfony\Component\Uid\Uuid;

/**
 * GET /api/users et GET /api/users/{id}.
 *
 * @implements ProviderInterface<UserResource>
 */
final readonly class UserProvider implements ProviderInterface
{
    public function __construct(private UserRepository $users)
    {
    }

    /**
     * @return UserResource|list<UserResource>|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): UserResource|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            return array_map(UserResource::fromEntity(...), $this->users->findBy([], ['email' => 'ASC']));
        }

        $id = (string) $uriVariables['id'];
        if (!Uuid::isValid($id)) {
            return null; // UUID invalide → 404
        }
        $user = $this->users->find($id);

        return null === $user ? null : UserResource::fromEntity($user);
    }
}
