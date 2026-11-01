<?php

declare(strict_types=1);

namespace App\Catalog\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Catalog\Api\Input\CreateRaceInput;
use App\Catalog\Api\Input\RenameRaceInput;
use App\Catalog\Api\State\RaceProcessor;
use App\Catalog\Repository\RaceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * Peuple qui conçoit vaisseaux et équipements (Argon, Paranid, Teladi…).
 *
 * Exposée directement par l'API : la lecture passe par le provider Doctrine d'API Platform
 * (seuls les champs du groupe race:read sont sérialisés), l'écriture par des DTO d'entrée et
 * RaceProcessor, pour préserver les règles métier de l'entité.
 */
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_PERMISSION_RACE_READ')"),
        new Get(security: "is_granted('ROLE_PERMISSION_RACE_READ')"),
        new Post(security: "is_granted('ROLE_PERMISSION_RACE_WRITE')", input: CreateRaceInput::class, processor: RaceProcessor::class),
        // Seul le nom est modifiable : PATCH (Content-Type: application/merge-patch+json) {"name": "..."}
        new Patch(security: "is_granted('ROLE_PERMISSION_RACE_WRITE')", input: RenameRaceInput::class, read: false, processor: RaceProcessor::class),
        new Delete(security: "is_granted('ROLE_PERMISSION_RACE_WRITE')", read: false, processor: RaceProcessor::class),
    ],
    normalizationContext: ['groups' => ['race:read']],
    order: ['code' => 'ASC'],
)]
#[ORM\Entity(repositoryClass: RaceRepository::class)]
#[ORM\Table(name: 'race')]
class Race
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[Groups(['race:read'])]
    private Uuid $id;

    #[ORM\Column(length: CatalogCode::MAX_LENGTH, unique: true)]
    #[Groups(['race:read'])]
    private string $code;

    #[ORM\Column(length: 120)]
    #[Groups(['race:read'])]
    private string $name;

    private function __construct(string $code, string $name)
    {
        $this->id = Uuid::v7();
        $this->code = CatalogCode::normalize($code);
        $this->rename($name);
    }

    public static function create(string $code, string $name): self
    {
        return new self($code, $name);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function rename(string $name): void
    {
        $this->name = CatalogCode::normalizeName($name);
    }
}
