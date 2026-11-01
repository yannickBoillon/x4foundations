<?php

declare(strict_types=1);

namespace App\Catalog\Api\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Catalog\Api\Input\CreateRaceInput;
use App\Catalog\Api\Input\RenameRaceInput;
use App\Catalog\Api\Resource\RaceResource;
use App\Catalog\Service\RaceManagement;

/**
 * Écriture sur /api/races : POST (création), PATCH (renommage), DELETE (suppression).
 *
 * @implements ProcessorInterface<CreateRaceInput|RenameRaceInput|null, RaceResource|null>
 */
final readonly class RaceProcessor implements ProcessorInterface
{
    public function __construct(private RaceManagement $races)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?RaceResource
    {
        if ($operation instanceof Delete) {
            $this->races->delete((string) $uriVariables['id']);

            return null;
        }

        return RaceResource::fromEntity(match (true) {
            $operation instanceof Post && $data instanceof CreateRaceInput => $this->races->create($data->code, $data->name),
            $operation instanceof Patch && $data instanceof RenameRaceInput => $this->races->rename((string) $uriVariables['id'], $data->name),
            default => throw new \LogicException(\sprintf('Opération "%s" non gérée par %s.', $operation->getName(), self::class)),
        });
    }
}
