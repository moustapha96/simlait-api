<?php

namespace App\Repository;

use App\Entity\UnitesUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UnitesUser>
 *
 * @method UnitesUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method UnitesUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method UnitesUser[]    findAll()
 * @method UnitesUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnitesUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UnitesUser::class);
    }

    public function add(UnitesUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UnitesUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAssociated(string $prenom, string $nom)
    {

        $qb = $this->createQueryBuilder('unites_user');

        $qb->addSelect('unites_user')
            ->addSelect('laiterie')->join('unites_user.unites', 'laiterie')

            ->addSelect('laiterie')

            ->where('laiterie.prenomProprietaire = :prenom')
            ->andWhere('laiterie.nomProprietaire = :nom')

            ->setParameter('prenom', $prenom)
            ->setParameter('nom', $nom)
            ->orderBy('laiterie.id', 'DESC');
        return $qb->getQuery()->getResult();
    }

    public function isAssociated(int $userMobile, int $laiterie)
    {
        $qb = $this->createQueryBuilder('unites_user');
        $qb->addSelect('unites_user')
            ->innerJoin('App\Entity\UserMobile', 'user', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites_user.userMobile = user')
            ->innerJoin('App\Entity\Unites', 'laiterie', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites_user.unites = laiterie')

            ->where('user.id = :userMobile')
            ->andWhere('laiterie.id = :laiterie')

            ->setParameter('userMobile', $userMobile)
            ->setParameter('laiterie', $laiterie)
            ->orderBy('laiterie.id', 'DESC');
        return $qb->getQuery()->getResult();
    }


    public function getLaiterieUser(int $idUser)
    {
        $qb = $this->createQueryBuilder('unites_user');
        $qb->innerJoin('App\Entity\Unites', 'lai', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites_user.unites = lai')
            ->select('lai')
            ->innerJoin('App\Entity\UserMobile', 'user', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites_user.userMobile = user')
            ->where('user.id = :userMobile')
            ->setParameter('userMobile', $idUser);

        return $qb->getQuery()->getResult();
    }

}
