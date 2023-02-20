<?php

namespace App\Repository;

use App\Entity\Conditionnements;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Conditionnements|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conditionnements|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conditionnements[]    findAll()
 * @method Conditionnements[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConditionnementsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conditionnements::class);
    }

    // /**
    //  * @return Conditionnements[] Returns an array of Conditionnements objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Conditionnements
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getCondiProduit(string $produit)
    {

        $qb = $this->createQueryBuilder('c');
        $qb->leftJoin('c.produits', 'p')
            ->where('p.nom = :pro')

            ->setParameter('pro', $produit);
        return $qb->getQuery()->getResult();
    }
}
