<?php

namespace App\Repository;

use App\Entity\UnitesDemandeSuivi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UnitesDemandeSuivi>
 *
 * @method UnitesDemandeSuivi|null find($id, $lockMode = null, $lockVersion = null)
 * @method UnitesDemandeSuivi|null findOneBy(array $criteria, array $orderBy = null)
 * @method UnitesDemandeSuivi[]    findAll()
 * @method UnitesDemandeSuivi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnitesDemandeSuiviRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UnitesDemandeSuivi::class);
    }

    public function add(UnitesDemandeSuivi $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UnitesDemandeSuivi $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return UnitesDemandeSuivi[] Returns an array of UnitesDemandeSuivi objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UnitesDemandeSuivi
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
