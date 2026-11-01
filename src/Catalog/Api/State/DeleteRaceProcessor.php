<?php

declare(strict_types=1);

namespace App\Catalog\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Catalog\Service\RaceManagement;

/**
 * @implements ProcessorInterface<mixed, null>
 */
final readonly class DeleteRaceProcessor implements ProcessorInterface
{
    public function __construct(private RaceManagement $races)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        $this->races->delete((string) $uriVariables['id']);

        return null;
    }
}
