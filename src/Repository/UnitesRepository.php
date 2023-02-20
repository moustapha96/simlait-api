<?php

namespace App\Repository;

use App\Entity\Unites;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Unites>
 *
 * @method Unites|null find($id, $lockMode = null, $lockVersion = null)
 * @method Unites|null findOneBy(array $criteria, array $orderBy = null)
 * @method Unites[]    findAll()
 * @method Unites[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnitesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unites::class);
    }

    public function add(Unites $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Unites $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAssociated(string $region, string $departement, string $zone, string $profil, int $idUserMobile)
    {

        if ($profil == "AGENT") {
            $qb = $this->createQueryBuilder('unites');
            $qb->addSelect('unites')
                ->innerJoin('App\Entity\Departement', 'departement', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.departement = departement')
                ->innerJoin('App\Entity\Region', 'region', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.region = region')
                ->innerJoin('App\Entity\Zones', 'zone', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.zone = zone')

                ->where('unites.isCertified = true')
                ->andWhere($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('departement.nom', ':departement'),
                    $qb->expr()->like('region.nom', ':region'),
                    $qb->expr()->like('zone.nom', ':zone'),

                )))
                ->setParameter('departement', '%' . $departement . '%')
                ->setParameter('region', '%' . $region . '%')
                ->setParameter('zone', '%' . $zone . '%')

                ->orderBy('unites.id', 'DESC');

            return $qb->getQuery()->getResult();
        } else {
            $qb = $this->createQueryBuilder('unites');
            $qb->addSelect('unites')
                ->innerJoin("App\Entity\Profils", 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.profil = profil')
                ->innerJoin("App\Entity\UserMobile", 'userMobile', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.userMobile = userMobile')

                ->where('unites.isCertified = true')
                ->andWhere("profil.nom = :profil_name ")
                ->andWhere("userMobile.id = :idUserMobile ")

                ->setParameter('profil_name', $profil)
                ->setParameter('idUserMobile', $idUserMobile)
                ->orderBy('unites.id', 'DESC');
            return $qb->getQuery()->getResult();
        }
    }


    public function findByDeparetement(
        string $department
    ) {
        $qb = $this->createQueryBuilder('col');
        $qb->addSelect('col')
            ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'col.departement = depart')
            ->where($qb->expr()->andX($qb->expr()->andX(
                $qb->expr()->like('depart.nom', ':departement'),
            )))
            ->setParameter('departement', '%' . $department . '%')
            ->orderBy('col.id', 'DESC');
        return $qb->getQuery()->getResult();
    }


    public function findLaiterieUser(string $email)
    {
        $qb = $this->createQueryBuilder('lait');

        $qb->addSelect('user')->join('lait.userMobile', 'user')
            ->where('user.email = :email')
            ->setParameter('email', $email)
            ->orderBy('lait.id', 'DESC');
        return $qb->getQuery()->getResult();
    }



    public function groupUnitesbyZone()
    {
        $qb = $this->createQueryBuilder('unite');
        $qb
            ->addSelect('zone')->join('unite.zone', 'zone')
            ->addSelect('zone.nom as name, count(unite.id) as nbre')
            ->groupBy('zone.nom')
            ->orderBy('unite.id', 'DESC');
        return $qb->getQuery()->getResult();
    }

    public function getUnitebyProfil(int $idProfil)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->addSelect('u')
            ->addSelect('zone')->join('u.zone', 'zone')
            ->addSelect('departement')->join('u.departement', 'departement')
            ->addSelect('region')->join('u.region', 'region')
            ->addSelect('userMobile')->join('u.userMobile', 'userMobile');
        $qb->leftJoin('u.profil', 'profil')
            ->where('profil.id = :idProfil')
            ->setParameter('idProfil', $idProfil);
        return $qb->getQuery()->getResult();
    }
}
