<?php

declare(strict_types=1);

namespace App\User\Api\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\User\Api\Input\AssignRoleInput;
use App\User\Api\Input\RegisterUserInput;
use App\User\Api\State\AssignRoleProcessor;
use App\User\Api\State\RegisterUserProcessor;
use App\User\Api\State\RevokeRoleProcessor;
use App\User\Api\State\UserProvider;
use App\User\Entity\User;

/**
 * Représentation HTTP d'un User : l'entité (et son hash de mot de passe) n'est jamais exposée directement.
 */
#[ApiResource(
    shortName: 'User',
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            provider: UserProvider::class,
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN') or object.email == user.getUserIdentifier()",
            provider: UserProvider::class,
        ),
        new Post(
            input: RegisterUserInput::class,
            processor: RegisterUserProcessor::class,
            openapi: new OpenApiOperation(security: []), // inscription publique
        ),
        // Affectation d'un rôle : POST /api/users/{id}/roles {"role": "ROLE_PILOT"}
        new Post(
            uriTemplate: '/users/{id}/roles',
            uriVariables: ['id'],
            status: 200,
            security: "is_granted('ROLE_ADMIN')",
            input: AssignRoleInput::class,
            processor: AssignRoleProcessor::class,
            name: 'user_assign_role',
        ),
        // Retrait d'un rôle : DELETE /api/users/{id}/roles/ROLE_PILOT
        new Delete(
            uriTemplate: '/users/{id}/roles/{role}',
            uriVariables: ['id', 'role'],
            security: "is_granted('ROLE_ADMIN')",
            read: false,
            processor: RevokeRoleProcessor::class,
            name: 'user_revoke_role',
        ),
    ],
)]
final class UserResource
{
    #[ApiProperty(identifier: true)]
    public string $id;

    public string $email;

    /** @var list<string> */
    public array $roles = [];

    public static function fromEntity(User $user): self
    {
        $resource = new self();
        $resource->id = $user->getId()->toRfc4122();
        $resource->email = $user->getEmail();
        $resource->roles = $user->getRoles();

        return $resource;
    }
}
