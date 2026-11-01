<?php

declare(strict_types=1);

namespace App\Catalog\Api\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Catalog\Api\Resource\RaceResource;
use App\Catalog\Repository\RaceRepository;
use Symfony\Component\Uid\Uuid;

/**
 * GET /api/races et GET /api/races/{id}.
 *
 * @implements ProviderInterface<RaceResource>
 */
final readonly class RaceProvider implements ProviderInterface
{
    public function __construct(private RaceRepository $repository)
    {
    }

    /**
     * @return RaceResource|list<RaceResource>|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): RaceResource|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            return array_map(RaceResource::fromEntity(...), $this->repository->findAllOrderedByCode());
        }

        $id = (string) $uriVariables['id'];
        if (!Uuid::isValid($id)) {
            return null; // UUID invalide → 404
        }
        $entity = $this->repository->find($id);

        return null === $entity ? null : RaceResource::fromEntity($entity);
    }
}
