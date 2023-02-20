<?php

namespace App\Repository;

use App\Entity\ConditionnementsProduitsUnites;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConditionnementsProduitsUnites>
 *
 * @method ConditionnementsProduitsUnites|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConditionnementsProduitsUnites|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConditionnementsProduitsUnites[]    findAll()
 * @method ConditionnementsProduitsUnites[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConditionnementsProduitsUnitesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConditionnementsProduitsUnites::class);
    }

    public function add(ConditionnementsProduitsUnites $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ConditionnementsProduitsUnites $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ConditionnementsProduitsUnites[] Returns an array of ConditionnementsProduitsUnites objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ConditionnementsProduitsUnites
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
