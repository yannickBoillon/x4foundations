<?php

declare(strict_types=1);

namespace App\User\Entity;

use App\User\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;

    #[ORM\Column(length: 180, unique: true)]
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
        return $this->email;
    }

    /**
     * @return list<string> rôles effectifs, ROLE_USER inclus
     */
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
