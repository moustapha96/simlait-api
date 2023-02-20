<?php

namespace App\Repository;

use App\Entity\Datas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Datas>
 *
 * @method Datas|null find($id, $lockMode = null, $lockVersion = null)
 * @method Datas|null findOneBy(array $criteria, array $orderBy = null)
 * @method Datas[]    findAll()
 * @method Datas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Datas::class);
    }

    public function add(Datas $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Datas $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function addRow(string $name)
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $query = $queryBuilder->update('datas', 'u')
            ->set('u.name', ':name')
            ->setParameter('name', $name)
            ->getQuery();

        $result = $query->execute();
    }

    //    /**
    //     * @return Datas[] Returns an array of Datas objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Datas
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
