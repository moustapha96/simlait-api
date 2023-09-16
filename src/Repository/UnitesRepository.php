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


    public function findAssociated(string $region, string $departement, string $zone, string $profil, int $idUserMobile, string $prenom, string $nom, $adresse)
    {

        $qb = $this->createQueryBuilder('unites');
        $qb->select('unites')
            ->innerJoin('App\Entity\Departement', 'departement', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.departement = departement')
            ->innerJoin('App\Entity\Region', 'region', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.region = region')
            ->innerJoin('App\Entity\Zones', 'zone', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.zone = zone')
            ->innerJoin("App\Entity\Profils", 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.profil = profil')
            ->innerJoin("App\Entity\UserMobile", 'userMobile', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.userMobile = userMobile')

            ->where('unites.isCertified = true')
            ->andWhere("profil.nom = :profil_name")
            ->andWhere($qb->expr()->andX($qb->expr()->andX(
                $qb->expr()->like('departement.nom', ':departement'),
                $qb->expr()->like('region.nom', ':region'),
                $qb->expr()->like('zone.nom', ':zone'),
            )))
            ->setParameter('departement', '%' . $departement . '%')
            ->setParameter('region', '%' . $region . '%')
            ->setParameter('zone', '%' . $zone . '%')
            ->setParameter('profil_name', $profil)
            ->orderBy('unites.id', 'DESC');

        return $qb->getQuery()->getResult();



    }
    public function findByCriteria(int $region, int $departement, int $zone, int $profil, int $userMobile, string $telephone) {

      if($zone != null && $zone != 0 ){
        $qb = $this->createQueryBuilder('unites');
        $qb->select('unites')
            ->innerJoin('App\Entity\Departement', 'departement', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.departement = departement')
            ->innerJoin('App\Entity\Region', 'region', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.region = region')
            ->innerJoin('App\Entity\Zones', 'zone', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.zone = zone')
            ->innerJoin("App\Entity\Profils", 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.profil = profil')
            ->innerJoin("App\Entity\UserMobile", 'userMobile', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.userMobile = userMobile')
            ->where('unites.telephone = :telephone')
            ->andWhere('departement.id = :departement')
            ->andWhere('region.id = :region')
            ->andWhere('zone.id = :zone')
            ->andWhere('profil.id = :profil')
            ->andWhere('userMobile.id = :userMobile')
            ->setParameter('departement', $departement)
            ->setParameter('region', $region)
            ->setParameter('zone',  $zone)
            ->setParameter('profil', $profil)
            ->setParameter('userMobile', $userMobile)
            ->setParameter('telephone', $telephone);

        return $qb->getQuery()->getOneOrNullResult();
      }else{

        $qb = $this->createQueryBuilder('unites');
        $qb->select('unites')
            ->innerJoin('App\Entity\Departement', 'departement', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.departement = departement')
            ->innerJoin('App\Entity\Region', 'region', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.region = region')
            ->innerJoin('App\Entity\Zones', 'zone', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.zone = zone')
            ->innerJoin("App\Entity\Profils", 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.profil = profil')
            ->innerJoin("App\Entity\UserMobile", 'userMobile', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.userMobile = userMobile')

            ->where('unites.telephone = :telephone')
            ->andWhere('departement.id = :departement')
            ->andWhere('region.id = :region')
            ->andWhere('profil.id = :profil')
            ->andWhere('userMobile.id = :userMobile')
            ->setParameter('departement', $departement)
            ->setParameter('region', $region)
            ->setParameter('profil', $profil)
            ->setParameter('userMobile', $userMobile)
            ->setParameter('telephone', $telephone);

        return $qb->getQuery()->getOneOrNullResult();
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

    public function findByPage(
        int $itemsPerPage,
        int $page
    ) {
        $qb = $this->createQueryBuilder('unite');
        $qb->select('unite')
            ->setFirstResult(($page) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->orderBy('unite.id', 'DESC');
        return $qb->getQuery()->getResult();
    }


    public function findWithStat(
        // int $itemsPerPage,
        // int $page
    )
    {
        $qb = $this->createQueryBuilder('unite');
        $qb->select('u')
            ->select('u.id as unite_id, u.nom as nom_unite')
            ->addSelect('COUNT(c.id) as total_collectes')
            ->addSelect('SUM(CASE WHEN c.isCertified = 1 THEN 1 ELSE 0 END) as nombre_collectes_certifiees')
            ->addSelect('SUM(CASE WHEN c.isCertified = 0 AND c.toCorrect = 0 AND c.isDeleted = 0 THEN 1 ELSE 0 END) as nombre_collectes_non_certifiees')
            ->addSelect('SUM(CASE WHEN c.toCorrect = 1 THEN 1 ELSE 0 END) as nombre_collectes_a_corriger')
            ->addSelect('SUM(CASE WHEN c.isDeleted = 1 THEN 1 ELSE 0 END) as nombre_collectes_supprimees')
            ->from(Unites::class, 'u')
            ->leftJoin('App\Entity\Collecte', 'c', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.unites = u.id')
            ->groupBy('u.id')

            // ->setFirstResult(($page) * $itemsPerPage)
            // ->setMaxResults($itemsPerPage)
            ->orderBy('u.id', 'DESC');
        return $qb->getQuery()->getResult();
    }
}
