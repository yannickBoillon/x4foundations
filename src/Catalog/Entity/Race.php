<?php

declare(strict_types=1);

namespace App\Catalog\Entity;

use App\Catalog\Repository\RaceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

/**
 * Peuple qui conçoit vaisseaux et équipements (Argon, Paranid, Teladi…).
 */
#[ORM\Entity(repositoryClass: RaceRepository::class)]
#[ORM\Table(name: 'race')]
class Race
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;

    #[ORM\Column(length: CatalogCode::MAX_LENGTH, unique: true)]
    private string $code;

    #[ORM\Column(length: 120)]
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
