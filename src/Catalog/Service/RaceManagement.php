<?php

declare(strict_types=1);

namespace App\Catalog\Service;

use App\Catalog\Entity\CatalogCode;
use App\Catalog\Entity\Race;
use App\Catalog\Exception\CodeAlreadyUsed;
use App\Catalog\Repository\RaceRepository;

/**
 * Gestion des races du référentiel : règles qui dépassent l'entité (unicité du code).
 */
final readonly class RaceManagement
{
    public function __construct(private RaceRepository $races)
    {
    }

    public function create(string $code, string $name): Race
    {
        $code = CatalogCode::normalize($code);
        if (null !== $this->races->findOneByCode($code)) {
            throw CodeAlreadyUsed::for('Race', $code);
        }

        $race = Race::create($code, $name);
        $this->races->save($race);

        return $race;
    }

    public function rename(string $id, string $name): Race
    {
        $race = $this->races->get($id);
        $race->rename($name);
        $this->races->save($race);

        return $race;
    }

    public function delete(string $id): void
    {
        $this->races->remove($this->races->get($id));
    }
}
