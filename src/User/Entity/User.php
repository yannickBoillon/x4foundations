<?php

declare(strict_types=1);

namespace App\User\Entity;

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
use App\User\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * Exposé directement par l'API : seuls id, email et rôles (groupe user:read) sont sérialisés,
 * jamais le hash du mot de passe. L'écriture passe par des DTO d'entrée et des processors.
 */
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Get(security: "is_granted('ROLE_ADMIN') or object.getUserIdentifier() == user.getUserIdentifier()"),
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
            read: false,
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
    normalizationContext: ['groups' => ['user:read']],
    order: ['email' => 'ASC'],
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[Groups(['user:read'])]
    private Uuid $id;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read'])]
    private string $email;

    /** @var list<string> rôles attribués explicitement (ROLE_USER est implicite) */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    private function __construct(
        string $email,
        #[ORM\Column(name: 'password_hash', length: 255)]
        private string $passwordHash,
    ) {
        $this->id = Uuid::v7();
        $this->changeEmail($email);
    }

    public static function register(string $email, string $passwordHash): self
    {
        return new self($email, $passwordHash);
    }

    public static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function changeEmail(string $email): void
    {
        $normalized = self::normalizeEmail($email);
        if (false === filter_var($normalized, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(\sprintf('"%s" n\'est pas un email valide.', $email));
        }
        $this->email = $normalized;
    }

    public function changePassword(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function hasRole(Role $role): bool
    {
        return \in_array($role->value, $this->getRoles(), true);
    }

    public function assignRole(Role $role): void
    {
        if ($this->hasRole($role)) {
            return;
        }
        $this->roles[] = $role->value;
    }

    public function revokeRole(Role $role): void
    {
        if (Role::User === $role) {
            throw new \DomainException('Le rôle ROLE_USER ne peut pas être retiré.');
        }
        $this->roles = array_values(array_filter($this->roles, static fn (string $r): bool => $r !== $role->value));
    }

    // --- Symfony Security ---

    public function getUserIdentifier(): string
    {
        \assert('' !== $this->email); // garanti par changeEmail()

        return $this->email;
    }

    /**
     * @return list<string> rôles effectifs, ROLE_USER inclus
     */
    #[Groups(['user:read'])]
    public function getRoles(): array
    {
        return [Role::User->value, ...$this->roles];
    }

    public function getPassword(): string
    {
        return $this->passwordHash;
    }

    public function eraseCredentials(): void
    {
    }
}
