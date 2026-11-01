<?php

declare(strict_types=1);

namespace App\Catalog\Api\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Catalog\Api\Input\CreateRaceInput;
use App\Catalog\Api\Input\RenameRaceInput;
use App\Catalog\Api\State\RaceProcessor;
use App\Catalog\Api\State\RaceProvider;
use App\Catalog\Entity\Race;

#[ApiResource(
    shortName: 'Race',
    uriTemplate: '/races',
    operations: [
        new GetCollection(security: "is_granted('ROLE_PERMISSION_RACE_READ')", provider: RaceProvider::class),
        new Post(security: "is_granted('ROLE_PERMISSION_RACE_WRITE')", input: CreateRaceInput::class, processor: RaceProcessor::class),
    ],
)]
#[ApiResource(
    shortName: 'Race',
    uriTemplate: '/races/{id}',
    uriVariables: ['id'],
    operations: [
        new Get(security: "is_granted('ROLE_PERMISSION_RACE_READ')", provider: RaceProvider::class),
        // Seul le nom est modifiable : PATCH (Content-Type: application/merge-patch+json) {"name": "..."}
        new Patch(security: "is_granted('ROLE_PERMISSION_RACE_WRITE')", input: RenameRaceInput::class, read: false, processor: RaceProcessor::class),
        new Delete(security: "is_granted('ROLE_PERMISSION_RACE_WRITE')", read: false, processor: RaceProcessor::class),
    ],
)]
final class RaceResource
{
    #[ApiProperty(identifier: true)]
    public string $id;

    public string $code;

    public string $name;

    public static function fromEntity(Race $entity): self
    {
        $resource = new self();
        $resource->id = $entity->getId()->toRfc4122();
        $resource->code = $entity->getCode();
        $resource->name = $entity->getName();

        return $resource;
    }
}
