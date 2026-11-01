<?php

declare(strict_types=1);

namespace App\User\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\User\Api\Input\RegisterUserInput;
use App\User\Entity\User;
use App\User\Service\UserRegistration;

/**
 * @implements ProcessorInterface<RegisterUserInput, User>
 */
final readonly class RegisterUserProcessor implements ProcessorInterface
{
    public function __construct(private UserRegistration $registration)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        return $this->registration->register($data->email, $data->plainPassword);
    }
}
