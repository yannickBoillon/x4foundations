<?php

declare(strict_types=1);

namespace App\Catalog\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Catalog\Api\Input\RenameRaceInput;
use App\Catalog\Api\Resource\RaceResource;
use App\Catalog\Service\RaceManagement;

/**
 * @implements ProcessorInterface<RenameRaceInput, RaceResource>
 */
final readonly class RenameRaceProcessor implements ProcessorInterface
{
    public function __construct(private RaceManagement $races)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): RaceResource
    {
        \assert($data instanceof RenameRaceInput);

        return RaceResource::fromEntity($this->races->rename((string) $uriVariables['id'], $data->name));
    }
}
