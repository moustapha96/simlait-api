<?php

namespace App\Repository;

use App\Entity\TableCounter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TableCounter>
 *
 * @method TableCounter|null find($id, $lockMode = null, $lockVersion = null)
 * @method TableCounter|null findOneBy(array $criteria, array $orderBy = null)
 * @method TableCounter[]    findAll()
 * @method TableCounter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TableCounterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TableCounter::class);
    }

    public function save(TableCounter $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TableCounter $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getRowCount(string $name): int
    {
        $queryBuilder = $this->createQueryBuilder('tc')
            ->select('tc.value')
            ->where('tc.name = :name')
            ->setParameter('name', $name);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
