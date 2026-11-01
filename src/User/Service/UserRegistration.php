<?php

declare(strict_types=1);

namespace App\User\Service;

use App\User\Entity\User;
use App\User\Exception\EmailAlreadyUsed;
use App\User\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

/**
 * Inscription d'un utilisateur, partagée par l'API et la commande app:user:create-admin.
 */
final readonly class UserRegistration
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasherFactoryInterface $hasherFactory,
    ) {
    }

    public function register(string $email, string $plainPassword): User
    {
        if (null !== $this->users->findOneByEmail($email)) {
            throw EmailAlreadyUsed::for(User::normalizeEmail($email));
        }

        $user = User::register($email, $this->hasherFactory->getPasswordHasher(User::class)->hash($plainPassword));
        $this->users->save($user);

        return $user;
    }
}
