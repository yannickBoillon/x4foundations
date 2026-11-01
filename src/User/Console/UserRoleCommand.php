<?php

declare(strict_types=1);

namespace App\User\Console;

use App\User\Entity\Role;
use App\User\Repository\UserRepository;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Exemples :
 *   bin/console app:user:role admin@fleet.test ROLE_ADMIN
 *   bin/console app:user:role pilote@fleet.test ROLE_PILOT --remove
 */
#[AsCommand(name: 'app:user:role', description: 'Ajoute ou retire un rôle à un utilisateur')]
final readonly class UserRoleCommand
{
    public function __construct(private UserRepository $users)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Email de l\'utilisateur')] string $email,
        #[Argument('Rôle (ex. ROLE_ADMIN)')] string $role,
        #[Option('Retirer le rôle au lieu de l\'ajouter')] bool $remove = false,
    ): int {
        $role = Role::tryFrom(strtoupper($role));
        if (null === $role) {
            $io->error(\sprintf('Rôle inconnu. Rôles possibles : %s', implode(', ', Role::values())));

            return Command::INVALID;
        }

        $user = $this->users->findOneByEmail($email);
        if (null === $user) {
            $io->error(\sprintf('Utilisateur "%s" introuvable.', $email));

            return Command::FAILURE;
        }

        try {
            $remove ? $user->revokeRole($role) : $user->assignRole($role);
        } catch (\DomainException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
        $this->users->save($user);

        $io->success(\sprintf('Rôles de %s : %s', $user->getEmail(), implode(', ', $user->getRoles())));

        return Command::SUCCESS;
    }
}
