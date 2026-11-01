<?php

declare(strict_types=1);

namespace App\User\Console;

use App\User\Entity\Role;
use App\User\Repository\UserRepository;
use App\User\Service\UserRegistration;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Exemples :
 *   bin/console app:user:create-admin admin@fleet.test               (mot de passe demandé)
 *   bin/console app:user:create-admin admin@fleet.test -n --password=secret123
 */
#[AsCommand(name: 'app:user:create-admin', description: 'Crée un utilisateur avec le rôle ROLE_ADMIN')]
final readonly class CreateAdminCommand
{
    private const int PASSWORD_MIN_LENGTH = 8;

    public function __construct(
        private UserRegistration $registration,
        private UserRepository $users,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Email de l\'administrateur')] string $email,
        #[Option('Mot de passe (demandé de façon masquée si absent)')] ?string $password = null,
    ): int {
        $password ??= $io->askHidden('Mot de passe', static function (?string $value): string {
            if (mb_strlen((string) $value) < self::PASSWORD_MIN_LENGTH) {
                throw new \RuntimeException(\sprintf('Le mot de passe doit faire au moins %d caractères.', self::PASSWORD_MIN_LENGTH));
            }

            return (string) $value;
        });

        if (null === $password || mb_strlen($password) < self::PASSWORD_MIN_LENGTH) {
            $io->error(\sprintf('Le mot de passe doit faire au moins %d caractères (option --password en mode non interactif).', self::PASSWORD_MIN_LENGTH));

            return Command::INVALID;
        }

        try {
            $user = $this->registration->register($email, $password);
        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());

            return Command::INVALID;
        } catch (\DomainException $e) {
            $io->error([$e->getMessage(), 'Pour promouvoir un compte existant : bin/console app:user:role <email> ROLE_ADMIN']);

            return Command::FAILURE;
        }

        $user->assignRole(Role::Admin);
        $this->users->save($user);

        $io->success(\sprintf(
            'Administrateur %s créé (id %s) — rôles : %s',
            $user->getEmail(),
            $user->getId(),
            implode(', ', $user->getRoles()),
        ));

        return Command::SUCCESS;
    }
}
