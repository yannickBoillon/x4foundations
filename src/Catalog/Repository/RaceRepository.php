<?php

declare(strict_types=1);

namespace App\Catalog\Repository;

use App\Catalog\Entity\Race;
use App\Catalog\Exception\CatalogEntryNotFound;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Race>
 */
final class RaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Race::class);
    }

    public function save(Race $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function remove(Race $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws CatalogEntryNotFound si l'id est inconnu ou n'est pas un UUID
     */
    public function get(string $id): Race
    {
        return (Uuid::isValid($id) ? $this->find($id) : null) ?? throw CatalogEntryNotFound::withId('Race', $id);
    }

    public function findOneByCode(string $code): ?Race
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * @return list<Race>
     */
    public function findAllOrderedByCode(): array
    {
        return $this->findBy([], ['code' => 'ASC']);
    }
}
