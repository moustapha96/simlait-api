<?php

namespace App\Repository;

use App\Doctrine\EntityManagerFactory;
use App\Entity\Collecte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Function\Month;

/**
 * @method Collecte|null find($id, $lockMode = null, $lockVersion = null)
 * @method Collecte|null findOneBy(array $criteria, array $orderBy = null)
 * @method Collecte[]    findAll()
 * @method Collecte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CollecteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collecte::class);
    }

    // /**
    //  * @return Collecte[] Returns an array of Collecte objects
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
    public function findOneBySomeField($value): ?Collecte
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getLowPrice()
    {
        $qb = $this->createQueryBuilder('col');
        $qb->addSelect('MIN (col.prix) as prix');
        return $qb->getQuery()->getResult();
    }

    public function getProduitPlusCollecter(string $dateDebut, string $dateFin)
    {
        if ($dateDebut != null && $dateFin != null) {
            $dd = new \DateTime($dateDebut);
            $df = new \DateTime($dateFin);
            $qb = $this->createQueryBuilder('col');

            $qb->addSelect('produit')->join('col.produits', 'produit')
                ->addSelect('produit.nom as name, count(col.id) as value')
                ->where($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->between('col.dateCollecte', ':dateDebut', ':dateFin'),
                )))
                ->groupBy('produit.nom')
                ->setParameter('dateDebut', $dd)
                ->setParameter('dateFin', $df)
                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        } else {
            $qb = $this->createQueryBuilder('col');
            $qb->addSelect('produit')->join('col.produits', 'produit')
                ->addSelect('produit.nom as name, count(col.id) as value')
                ->groupBy('produit.nom')
                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        }
    }

    public function groupCollectebyZone(
        string $dateDebut,
        string $dateFin
    ) {
        if ($dateDebut != null && $dateFin != null) {
            $dd = new \DateTime($dateDebut);
            $df = new \DateTime($dateFin);
            $qb = $this->createQueryBuilder('col');
            $qb->addSelect('lait')->join('col.unites', 'lait')
                ->addSelect('zone')->join('lait.zone', 'zone')
                ->addSelect('zone.nom as name, count(col.id) as value')
                ->where($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->between('col.dateCollecte', ':dateDebut', ':dateFin'),
                )))
                ->groupBy('zone.nom')
                ->setParameter('dateDebut', $dd)
                ->setParameter('dateFin', $df)
                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        } else {
            $qb = $this->createQueryBuilder('col');
            $qb->addSelect('lait')->join('col.unites', 'lait')
                ->addSelect('zone')->join('lait.zone', 'zone')
                ->addSelect('zone.nom as name, count(col.id) as value')
                ->groupBy('zone.nom')
                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        }
    }

    public function groupCollecteByDate(
        string $dateDebut,
        string $dateFin
    ) {
        if ($dateDebut != null && $dateFin != null) {
            $dd = new \DateTime($dateDebut);
            $df = new \DateTime($dateFin);
            $qb = $this->createQueryBuilder('col');
            $qb->addSelect(' DAY( col.dateCollecte) as mois, count(col.id) as value')
                ->where($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->between('col.dateCollecte', ':dateDebut', ':dateFin'),
                )))
                ->groupBy('mois')
                ->setParameter('dateDebut', $dd)
                ->setParameter('dateFin', $df)
                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        } else {
            $qb = $this->createQueryBuilder('col');
            $qb->addSelect(' DAY( col.dateCollecte) as mois, count(col.id) as value')
                ->groupBy('mois')
                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        }
    }


    public function findCertified(
        string $dateDebut,
        string $dateFin
    ) {
        if ($dateDebut != null && $dateFin != null) {
            $dd = new \DateTime($dateDebut);
            $df = new \DateTime($dateFin);
            $qb = $this->createQueryBuilder('c');
            $qb->where('c.isCertified = true')
                ->where($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->between('c.dateCollecte', ':dateDebut', ':dateFin'),
                )))
                ->orderBy('c.id', 'DESC')
                ->setParameter('dateDebut', $dd)
                ->setParameter('dateFin', $df)
                // ->setMaxResults(10)
                ->getQuery()
                ->getResult();
            return $qb->getQuery()->getResult();
        } else {
            $qb = $this->createQueryBuilder('c');
            $qb->where('c.isCertified = true')
                ->orderBy('c.id', 'DESC')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
            return $qb->getQuery()->getResult();
        }
    }


    public function findParCriteria(
        string $profil,
        string $region,
        string $department,
        string $zone,
        string $produit,
        string $conditionnement,
        string $unites,
        string $emballage,
        string $dateDebut,
        string $dateFin
    ) {


        if ($dateDebut != null && $dateFin != null) {
            $dd = new \DateTime($dateDebut);
            $df = new \DateTime($dateFin);

            $qb = $this->createQueryBuilder('col');
            $qb->addSelect('condi')->join('col.conditionnements', 'condi')
                ->addSelect('lait')->join('col.unites', 'lait')
                ->addSelect('emb')->join('col.emballages', 'emb')
                ->addSelect('prod')->join('col.produits', 'prod')

                ->addSelect('profil')->join('lait.profil', 'profil')

                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.departement = depart')

                ->where('profil.nom = :profil')
                ->andWhere($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('prod.nom', ':produit'),
                    $qb->expr()->like('condi.nom', ':conditionnement'),
                    $qb->expr()->like('lait.nom', ':laiterie'),
                    $qb->expr()->like('emb.nom', ':emballage'),
                    $qb->expr()->like('zon.nom', ':zone'),
                    $qb->expr()->like('depart.nom', ':departement'),
                    $qb->expr()->like('reg.nom', ':region'),
                    $qb->expr()->between('col.dateCollecte', ':dateDebut', ':dateFin'),

                )))
                ->setParameter('produit', '%' . $produit . '%')
                ->setParameter('conditionnement', '%' . $conditionnement . '%')
                ->setParameter('laiterie', '%' . $unites . '%')
                ->setParameter('zone', '%' . $zone . '%')
                ->setParameter('departement', '%' . $department . '%')
                ->setParameter('emballage', '%' . $emballage . '%')
                ->setParameter('region', '%' . $region . '%')
                ->setParameter('dateDebut', $dd)
                ->setParameter('dateFin', $df)
                ->setParameter('profil', $profil)
                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        } else {
            $qb = $this->createQueryBuilder('col');
            $qb->addSelect('condi')->join('col.conditionnements', 'condi')
                ->addSelect('lait')->join('col.unites', 'lait')
                ->addSelect('emb')->join('col.emballages', 'emb')
                ->addSelect('prod')->join('col.produits', 'prod')
                ->addSelect('profil')->join('lait.profil', 'profil')


                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.departement = depart')

                ->where('profil.nom = :profil')
                ->andWhere($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('prod.nom', ':produit'),
                    $qb->expr()->like('condi.nom', ':conditionnement'),
                    $qb->expr()->like('lait.nom', ':laiterie'),
                    $qb->expr()->like('emb.nom', ':emballage'),
                    $qb->expr()->like('zon.nom', ':zone'),
                    $qb->expr()->like('depart.nom', ':departement'),
                    $qb->expr()->like('reg.nom', ':region'),
                )))

                ->setParameter('produit', '%' . $produit . '%')
                ->setParameter('conditionnement', '%' . $conditionnement . '%')
                ->setParameter('laiterie', '%' . $unites . '%')
                ->setParameter('zone', '%' . $zone . '%')
                ->setParameter('departement', '%' . $department . '%')
                ->setParameter('emballage', '%' . $emballage . '%')
                ->setParameter('region', '%' . $region . '%')
                ->setParameter('profil', $profil)
                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        }
    }


    public function findLast(
        string $dateDebut,
        string $dateFin
    ) {
        if ($dateDebut != null && $dateFin != null) {
            $dd = new \DateTime($dateDebut);
            $df = new \DateTime($dateFin);

            $qb = $this->createQueryBuilder('col');

            $qb->where($qb->expr()->andX($qb->expr()->andX(
                $qb->expr()->between('col.dateCollecte', ':dateDebut', ':dateFin'),
            )))
                ->setParameter('dateDebut', $dd)
                ->setParameter('dateFin', $df)
                ->setMaxResults(10)
                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        } else {
            return $this->createQueryBuilder('c')
                ->orderBy('c.id', 'DESC')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
        }
    }

    public function groupPCD(
        string $dateDebut,
        string $dateFin
    ) {

        if ($dateDebut != null && $dateFin != null) {
            $dd = new \DateTime($dateDebut);
            $df = new \DateTime($dateFin);
            $qb = $this->createQueryBuilder('col');
            $qb->addSelect('condi')->join('col.conditionnements', 'condi')
                ->addSelect('condi.nom as conditionnement')
                ->addSelect('lait')->join('col.unites', 'lait')
                ->addSelect('emb')->join('col.emballages', 'emb')
                ->addSelect('prod')->join('col.produits', 'prod')
                ->addSelect('prod.nom as produit')
                ->addSelect('MAX (col.prix) as prix_max')
                ->addSelect('MIN (col.prix) as prix_min')
                ->where($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->between('col.dateCollecte', ':dateDebut', ':dateFin'),
                )))
                ->where('col.isCertified = true')
                ->groupBy('condi.nom')
                ->addGroupBy('produit')
                ->setParameter('dateDebut', $dd)
                ->setParameter('dateFin', $df)
                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        } else {
            $qb = $this->createQueryBuilder('col');
            $qb->addSelect('condi')->join('col.conditionnements', 'condi')
                ->addSelect('condi.nom as conditionnement')
                ->addSelect('lait')->join('col.unites', 'lait')
                ->addSelect('emb')->join('col.emballages', 'emb')
                ->addSelect('prod')->join('col.produits', 'prod')
                ->addSelect('prod.nom as produit')
                ->addSelect('col as collecte')
                ->addSelect('MAX (col.prix) as prix_max')
                ->addSelect('MIN (col.prix) as prix_min')
                ->addSelect('SUM(col.quantite) as quantite_total')
                ->where('col.isCertified = true')
                ->groupBy('prod.id')
                // ->addGroupBy('produit')
                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        }
    }

    public function findGroupPCD(
        string $region,
        string $department,
        string $zone,
        string $dateDebut,
        string $dateFin
    ) {


        if ($dateDebut != null && $dateFin != null) {
            $dd = new \DateTime($dateDebut);
            $df = new \DateTime($dateFin);

            $qb = $this->createQueryBuilder('col');
            $qb->addSelect('condi')->join('col.conditionnements', 'condi')
                ->addSelect('condi.nom as conditionnement')
                ->addSelect('lait')->join('col.unites', 'lait')
                ->addSelect('emb')->join('col.emballages', 'emb')
                ->addSelect('prod')->join('col.produits', 'prod')

                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.departement = depart')



                ->addSelect('prod.nom as produit')
                ->addSelect('MAX (col.prix) as prix_max')
                ->addSelect('MIN (col.prix) as prix_min')
                ->where($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('zon.nom', ':zone'),
                    $qb->expr()->like('depart.nom', ':departement'),
                    $qb->expr()->like('reg.nom', ':region'),
                    $qb->expr()->between('col.dateCollecte', ':dateDebut', ':dateFin'),
                )))
                ->groupBy('condi.nom')
                ->addGroupBy('produit')
                ->setParameter('zone', '%' . $zone . '%')
                ->setParameter('departement', '%' . $department . '%')
                ->setParameter('region', '%' . $region . '%')
                ->setParameter('dateDebut', $dd)
                ->setParameter('dateFin', $df)
                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        } else {
            $qb = $this->createQueryBuilder('col');
            $qb->addSelect('condi')->join('col.conditionnements', 'condi')
                ->addSelect('condi.nom as conditionnement')
                ->addSelect('lait')->join('col.unites', 'lait')
                ->addSelect('emb')->join('col.emballages', 'emb')
                ->addSelect('prod')->join('col.produits', 'prod')

                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.departement = depart')

                ->addSelect('prod.nom as produit')
                ->addSelect('col as collecte')
                ->addSelect('MAX (col.prix) as prix_max')
                ->addSelect('MIN (col.prix) as prix_min')
                ->addSelect('SUM(col.quantite) as quantite_total')
                ->where($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('zon.nom', ':zone'),
                    $qb->expr()->like('depart.nom', ':departement'),
                    $qb->expr()->like('reg.nom', ':region'),
                )))

                ->groupBy('condi.nom')
                ->addGroupBy('produit')
                ->setParameter('zone', '%' . $zone . '%')
                ->setParameter('departement', '%' . $department . '%')
                ->setParameter('region', '%' . $region . '%')
                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        }
    }

    public function findPC(
        string $produit,
        string $conditionnement,
        string $emballage,
        string $profil,
        string $region,
        string $departement,
        string $zone,
        string $groupBy,
        $dateDebut,
        $dateFin
    ) {

        $dd = new \DateTime($dateDebut);
        $df = new \DateTime($dateFin);

        $qb = $this->createQueryBuilder('collecte');

        $qb->select('prod.nom  as produit, prod.unite')
            ->addSelect('unit.nom as unites')
            ->addSelect('reg.nom as region')
            ->addSelect('depart.nom as departement')
            ->addSelect('zon.nom as zone')

            ->addSelect('condi.nom as conditionnement')
            ->addSelect('collecte.prix as prix')
            ->addSelect('collecte.quantite as quantite')
            ->addSelect('collecte.quantite_vendu as quantite_vendu')
            ->addSelect('collecte.quantite_autre as quantite_autre')
            ->addSelect('collecte.quantite_perdu as quantite_perdu')
            ->addSelect('emb.nom as emballage')

            ->where('collecte.isCertified = true')
            ->andWhere($qb->expr()->andX($qb->expr()->andX(
                $qb->expr()->like('prod.nom', ':produit'),
                $qb->expr()->like('condi.nom', ':conditionnement'),
                $qb->expr()->like('emb.nom', ':emballages'),
                $qb->expr()->like('profil.nom', ':profil'),
                $qb->expr()->like('reg.nom', ':region'),
                $qb->expr()->like('depart.nom', ':departement'),
                $qb->expr()->like('zon.nom', ':zone'),
                $qb->expr()->between('collecte.dateCollecte', ':dateDebut', ':dateFin'),
            )))

            ->innerJoin('App\Entity\Produits', 'prod', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.produits = prod')
            ->innerJoin('App\Entity\Unites', 'unit', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unit')
            ->innerJoin('App\Entity\Emballage', 'emb', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.emballages = emb')
            ->innerJoin('App\Entity\Conditionnements', 'condi', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.conditionnements = condi')


            // ->leftJoin('collecte.produits', 'prod')
            // ->leftJoin('collecte.unites', 'unit')
            // ->leftJoin('collecte.emballages', 'emb')
            // ->leftJoin('collecte.conditionnements', 'condi')

            ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
            ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
            ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart')
            ->innerJoin('App\Entity\Profils', 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.profil = profil ')

            ->setParameter('produit', '%' . $produit . '%')
            ->setParameter('conditionnement', '%' . $conditionnement . '%')
            ->setParameter('emballages', '%' . $emballage . '%')
            ->setParameter('profil', '%' . $profil . '%')
            ->setParameter('zone', '%' . $zone . '%')
            ->setParameter('departement', '%' . $departement . '%')
            ->setParameter('region', '%' . $region . '%')
            ->setParameter('dateDebut', $dd)
            ->setParameter('dateFin', $df);

        // if ($groupBy == "produit") {
        //     $qb->groupBy('produit');
        // } else if ($groupBy == "conditionnement") {
        //     $qb->groupBy('conditionnement');
        // } else if ($groupBy == "profil") {
        //     $qb->groupBy('profil');
        // }

        $qb->orderBy('collecte.id', 'DESC');
        return $qb->getQuery()->getResult();
    }


    public function searchPC(string $produit, string $conditionnement, string $region, string $departement, string $zone, string $dateDebut, string $dateFin)
    {
        if ($dateDebut != null && $dateFin != null) {
            $dd = new \DateTime($dateDebut);
            $df = new \DateTime($dateFin);

            $qb = $this->createQueryBuilder('col');
            $qb->addSelect('condi')->join('col.conditionnements', 'condi')
                ->addSelect('emb')->join('col.emballages', 'emb')
                ->addSelect('prod')->join('col.produits', 'prod')
                ->addSelect('lait')->join('col.unites', 'lait')


                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.departement = depart')


                ->addSelect('col.quantite as quantite')
                ->addSelect('emb.nom as emballage')
                ->addSelect('condi.nom as conditionnement')
                ->addSelect('lait.nom as laiterie')

                ->addSelect('reg.nom as region')
                ->addSelect('depart.nom as departement')
                ->addSelect('zon.nom as zone')

                ->addSelect('col.prix as prix')
                ->addSelect('prod.nom as produit')

                ->where($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('zon.nom', ':zone'),
                    $qb->expr()->like('depart.nom', ':departement'),
                    $qb->expr()->like('reg.nom', ':region'),
                    $qb->expr()->between('col.dateCollecte', ':dateDebut', ':dateFin'),
                )))
                ->setParameter('zone', '%' . $zone . '%')
                ->setParameter('departement', '%' . $departement . '%')
                ->setParameter('region', '%' . $region . '%')
                ->setParameter('dateDebut', $dd)
                ->setParameter('dateFin', $df)

                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        } else {

            $qb = $this->createQueryBuilder('col');
            $qb->addSelect('condi')->join('col.conditionnements', 'condi')
                ->addSelect('emb')->join('col.emballages', 'emb')
                ->addSelect('prod')->join('col.produits', 'prod')
                ->addSelect('lait')->join('col.unites', 'lait')

                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'lait.departement = depart')


                ->addSelect('col.quantite as quantite')
                ->addSelect('emb.nom as emballage')
                ->addSelect('condi.nom as conditionnement')
                ->addSelect('lait.nom as laiterie')

                ->addSelect('reg.nom as region')
                ->addSelect('depart.nom as departement')
                ->addSelect('zon.nom as zone')

                ->addSelect('col.prix as prix')
                ->addSelect('prod.nom as produit')

                ->where($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('zon.nom', ':zone'),
                    $qb->expr()->like('depart.nom', ':departement'),
                    $qb->expr()->like('reg.nom', ':region'),
                )))

                ->setParameter('zone', '%' . $zone . '%')
                ->setParameter('departement', '%' . $departement . '%')
                ->setParameter('region', '%' . $region . '%')
                ->orderBy('col.id', 'DESC');
            return $qb->getQuery()->getResult();
        }
    }


    public function findUnitesWithDemandeUnites(string $zone, int $besoin, string $produit, string $dateDebut, string $dateFin)
    {
        $dd = new \DateTime($dateDebut);
        $df = new \DateTime($dateFin);

        $qb = $this->createQueryBuilder('collecte');

        $qb->leftJoin('collecte.produits', 'prod')
            ->leftJoin('collecte.unites', 'unit')
            ->leftJoin('collecte.emballages', 'emb')
            ->leftJoin('collecte.conditionnements', 'condi')


            ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
            ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
            ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart');


        $qb->select('collecte.quantite as quantite')
            ->addSelect('emb.nom as emballage')
            ->addSelect('condi.nom as conditionnement')
            ->addSelect('unit.nom as unites')
            ->addSelect('unit.id as idUnites')
            ->addSelect('reg.nom as region')
            ->addSelect('depart.nom as departement')
            ->addSelect('zon.nom as zone')
            ->addSelect('unit.telephone as telephone')
            ->addSelect('unit.adresse as adresse')
            ->addSelect('collecte.prix as prix')
            ->addSelect('prod.nom as produit')

            ->where('collecte.isCertified = true')

            ->andWhere('collecte.quantite >= :besoin')
            ->andWhere($qb->expr()->andX($qb->expr()->andX(
                $qb->expr()->like('zon.nom', ':zo'),
                $qb->expr()->like('prod.nom', ':produit'),
                $qb->expr()->between('collecte.dateCollecte', ':dateDebut', ':dateFin'),
            )))

            ->setParameter('dateDebut', $dd)
            ->setParameter('dateFin', $df)
            ->setParameter('zo', '%' . $zone . '%')
            ->setParameter('besoin',  $besoin)
            ->setParameter('produit', '%' . $produit . '%');

        // ->groupBy('prod.nom');

        return $qb->getQuery()->getResult();
    }
    public function findUnitesWithDemandeUnitesAutre(string $zone, int $besoin, string $produit, string $dateDebut, string $dateFin)
    {
        $dd = new \DateTime($dateDebut);
        $df = new \DateTime($dateFin);


        $qb = $this->createQueryBuilder('collecte');

        $qb->leftJoin('collecte.produits', 'prod')
            ->leftJoin('collecte.emballages', 'emb')
            ->leftJoin('collecte.conditionnements', 'condi')
            ->leftJoin('collecte.unitesAutre',  'unitesAutre')


            ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
            ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
            ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart');


        $qb->select('collecte.quantite as quantite')
            ->addSelect('emb.nom as emballage')
            ->addSelect('condi.nom as conditionnement')
            ->addSelect('unitesAutre.nom as unitesAutre')
            ->addSelect('unitesAutre.id as idUnitesAutre')
            ->addSelect('reg.nom as region')
            ->addSelect('depart.nom as departement')
            ->addSelect('zon.nom as zone')
            ->addSelect('unitesAutre.telephone as telephone')
            ->addSelect('unitesAutre.adresse as adresse')
            ->addSelect('collecte.prix as prix')
            ->addSelect('prod.nom as produit')

            ->where('collecte.isCertified = true')

            ->andWhere('collecte.quantite >= :besoin')
            ->andWhere($qb->expr()->andX($qb->expr()->andX(

                $qb->expr()->like('prod.nom', ':produit'),
                $qb->expr()->between('collecte.dateCollecte', ':dateDebut', ':dateFin'),
            )))

            ->setParameter('dateDebut', $dd)
            ->setParameter('dateFin', $df)

            ->setParameter('besoin',  $besoin)
            ->setParameter('produit', '%' . $produit . '%');

        // ->groupBy('prod.nom');

        return $qb->getQuery()->getResult();
    }
    public function  findCollecteEleveur()
    {
        $qb = $this->createQueryBuilder('collecte');
        $qb->select('collecte');
        $qb->addSelect('unites')->join('collecte.unites', 'unites')
            ->addSelect('profil')->join('unites.profil', 'profil')
            ->where('profil.id = :profil')
            ->setParameter('profil', 5)

            ->orderBy('collecte.id', 'DESC');
        return $qb->getQuery()->getResult();
    }
    public function  findCollecteProducteur()
    {
        $qb = $this->createQueryBuilder('collecte');
        $qb->select('collecte');
        $qb->addSelect('unites')->join('collecte.unites', 'unites')
            ->addSelect('profil')->join('unites.profil', 'profil')
            ->where('profil.id = :profil')
            ->setParameter('profil', 6)

            ->orderBy('collecte.id', 'DESC');
        return $qb->getQuery()->getResult();
    }
    public function  findCollecteCollecteur()
    {
        $qb = $this->createQueryBuilder('collecte');
        $qb->select('collecte');
        $qb->addSelect('unites')->join('collecte.unites', 'unites')
            ->addSelect('profil')->join('unites.profil', 'profil')
            ->where('profil.id = :profil')
            ->setParameter('profil', 4)

            ->orderBy('collecte.id', 'DESC');
        return $qb->getQuery()->getResult();
    }
    public function  findCollecteCommercants()
    {
        $qb = $this->createQueryBuilder('collecte');
        $qb->addSelect('unites')->join('collecte.unites', 'unites')
            ->addSelect('profil')->join('unites.profil', 'profil')
            ->where('profil.id = :profil')
            ->setParameter('profil', 3)

            ->orderBy('collecte.id', 'DESC');
        return $qb->getQuery()->getResult();
    }
    public function  findCollecteTransformateur()
    {
        $qb = $this->createQueryBuilder('collecte');
        $qb->select('collecte');
        $qb->addSelect('unites')->join('collecte.unites', 'unites')
            ->addSelect('profil')->join('unites.profil', 'profil')
            ->where('profil.id = :profil')
            ->setParameter('profil', 7)

            ->orderBy('collecte.id', 'DESC');
        return $qb->getQuery()->getResult();
    }



    // grouper les collecte par profil

    public function  groupCollectebyProfil()
    {
        $qb = $this->createQueryBuilder('collecte');
        $qb->select('collecte');
        $qb->addSelect('unites')->join('collecte.unites', 'unites')
            ->addSelect('profil')->join('unites.profil', 'profil')
            ->addSelect('profil.nom as name, count(collecte.id) as nbre')
            ->orderBy('collecte.id', 'DESC');
        return $qb->getQuery()->getResult();
    }



    public function getCollecteNowMonth()
    {
        $time = new \DateTime();
        $datenow =  $time->format('Y-m-d  00:00:00');
        $datenowm = $time->format('Y-m-d  23:59:59');

        $qb = $this->createQueryBuilder('collecte');
        $qb->select('count(collecte.id) as nbre')
            ->where('collecte.dateCollecte  BETWEEN :datemin AND  :datemax  ')
            ->setParameter('datemin', $datenow)
            ->setParameter('datemax', $datenowm);

        return $qb->getQuery()->getResult();
    }

    public function getCollectepreviousMonth()
    {
        $datemin = date('Y-m-d 00:00:00', strtotime("-1 day"));
        $datemax = date('Y-m-d 23:59:59', strtotime("-1 day"));

        $qb = $this->createQueryBuilder('collecte');
        $qb->select('count(collecte.id) as nbre')
            ->where('collecte.dateCollecte  BETWEEN  :datemin AND :datemax  ')
            ->setParameter('datemin', $datemin)
            ->setParameter('datemax', $datemax);

        return $qb->getQuery()->getResult();
    }


    public function getUniteNowMonth()
    {

        $time = new \DateTime();
        $datenow =  $time->format('Y-m-d  00:00:00');
        $datenowm = $time->format('Y-m-d  23:59:59');
        $qb = $this->createQueryBuilder('collecte');
        $qb->innerJoin('App\Entity\Unites', 'unites', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unites')
            ->select('count(unites.id) as nbre')
            ->where('collecte.dateCollecte  BETWEEN :datemin AND  :datemax  ')
            ->setParameter('datemin', $datenow)
            ->setParameter('datemax', $datenowm);

        return $qb->getQuery()->getResult();
    }

    public function getUnitepreviousMonth()
    {
        $time = new \DateTime();
        $datemin = date('Y-m-d 00:00:00', strtotime("-1 day"));
        $datemax = date('Y-m-d 23:59:59', strtotime("-1 day"));

        $qb = $this->createQueryBuilder('collecte');
        $qb->innerJoin('App\Entity\Unites', 'unites', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unites')

            ->select('count(unites.id) as nbre')
            ->where('collecte.dateCollecte  BETWEEN :datemin AND  :datemax  ')
            ->setParameter('datemin', $datemin)
            ->setParameter('datemax', $datemax);

        return $qb->getQuery()->getResult();
    }


    /// le type de profil le plus collecter 
    public function getProduitPlusCollecterNow()
    {
        $time = new \DateTime();
        $datenow =  $time->format('Y-m-d  00:00:00');
        $datenowm = $time->format('Y-m-d  23:59:59');

        $qb = $this->createQueryBuilder('collecte');
        $qb->innerJoin('App\Entity\Unites', 'unites', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unites')
            ->innerJoin('App\Entity\Profils', 'pro', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.profil = pro')

            ->select('count(pro.id) as nbre')
            ->addSelect('pro.nom as profil')
            ->where('collecte.dateCollecte  BETWEEN :datemin AND  :datemax  ')
            ->setParameter('datemin', $datenow)
            ->setParameter('datemax', $datenowm)
            ->groupBy('pro.nom');
        return $qb->getQuery()->getResult();
    }

    /// le type de profil le plus collecter 
    public function getProduitPlusCollectePrevious()
    {
        $time = new \DateTime();
        $datemin = date('Y-m-d 00:00:00', strtotime("-1 day"));
        $datemax = date('Y-m-d 23:59:59', strtotime("-1 day"));

        $qb = $this->createQueryBuilder('collecte');
        $qb->innerJoin('App\Entity\Unites', 'unites', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unites')
            ->innerJoin('App\Entity\Profils', 'pro', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.profil = pro')

            ->select('count(pro.id) as nbre')
            ->addSelect('pro.nom as profil')
            ->where('collecte.dateCollecte  BETWEEN :datemin AND  :datemax  ')
            ->setParameter('datemin', $datemin)
            ->setParameter('datemax', $datemax)
            ->groupBy('pro.nom');
        return $qb->getQuery()->getResult();
    }




    /// le type de profil le plus collecter 
    public function getProduitByProfil()
    {

        $qb = $this->createQueryBuilder('collecte');
        $qb->innerJoin('App\Entity\Unites', 'unites', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unites')
            ->innerJoin('App\Entity\Profils', 'pro', \Doctrine\ORM\Query\Expr\Join::WITH, 'unites.profil = pro')

            ->select('count(pro.id) as nbre')
            ->addSelect('pro.nom as profil')
            ->groupBy('pro.nom');
        return $qb->getQuery()->getResult();
    }


    // le produit le plus collecte
    public function getProduitlePlusCollectes()
    {

        $qb = $this->createQueryBuilder('collecte');
        $qb
            ->innerJoin('App\Entity\Produits', 'pro', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.produits = pro')

            ->select('count(pro.id) as nbre')
            ->addSelect('pro.nom as produit')
            ->groupBy('pro.nom');
        return $qb->getQuery()->getResult();
    }

    //quantite collecte
    public function getCollecteQuantiteByDate()
    {

        $qb = $this->createQueryBuilder('collecte');
        $qb->innerJoin('App\Entity\Produits', 'pro', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.produits = pro')

            ->select('SUM(collecte.quantite) as quantiteTotal')
            ->addSelect('pro.nom as produit')
            ->addSelect("DATE_FORMAT(collecte.dateCollecte, '%Y-%m-%d')  as date")


            ->groupBy('pro.nom')
            ->groupBy("collecte.dateCollecte");
        return $qb->getQuery()->getResult();
    }

    // agregation By Produit : production grouper par produit
    public function agregationByProduit(
        string $produit,
        string $conditionnement,
        string $emballage,
        string $profil,
        string $dateDebut,
        string $dateFin,
        string $region,
        string $department,
        string $zone,
    ) {
        if ($dateDebut != null && $dateFin != null) {
            $dd = new \DateTime($dateDebut);
            $df = new \DateTime($dateFin);

            $qb = $this->createQueryBuilder('collecte');
            $qb->select('prod.nom  as produit, prod.unite')
                ->addSelect('MAX (collecte.prix) as prix_max')
                ->addSelect('MIN (collecte.prix) as prix_min')
                ->addSelect('SUM(collecte.quantite) as quantite_total')
                ->addSelect('SUM(collecte.quantite_perdu) as quantite_perdu')
                ->addSelect('SUM(collecte.quantite_autre) as quantite_autre')
                ->addSelect('SUM(collecte.quantite_vendu) as quantite_vendu')
                ->addSelect('condi.nom as conditionnement')
                ->where('collecte.isCertified = true')

                ->andWhere($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('zon.nom', ':zone'),
                    $qb->expr()->like('depart.nom', ':departement'),
                    $qb->expr()->like('reg.nom', ':region'),
                    $qb->expr()->like('profil.nom', ':profils'),
                    $qb->expr()->like('prod.nom', ':produits'),
                    $qb->expr()->between('collecte.dateCollecte', ':dateDebut', ':dateFin'),
                )))

                // ->leftJoin('collecte.produits', 'prod')
                // ->leftJoin('collecte.unites', 'unit')
                // ->leftJoin('collecte.emballages', 'emb')
                // ->leftJoin('collecte.conditionnements', 'condi')
                ->innerJoin('App\Entity\Produits', 'prod', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.produits = prod')
                ->innerJoin('App\Entity\Unites', 'unit', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unit')
                ->innerJoin('App\Entity\Emballage', 'emb', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.emballages = emb')
                ->innerJoin('App\Entity\Conditionnements', 'condi', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.conditionnements = condi')

                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart')
                ->innerJoin('App\Entity\Profils', 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.profil = profil ')

                ->setParameter('dateDebut', $dd)
                ->setParameter('dateFin', $df)
                ->setParameter('zone', '%' . $zone . '%')
                ->setParameter('departement', '%' . $department . '%')
                ->setParameter('region', '%' . $region . '%')
                ->setParameter('profils', '%' . $profil . '%')
                ->setParameter('produits', '%' . $produit . '%')


                ->distinct('condi.nom')
                ->groupBy('prod.nom')
                ->addGroupBy('condi.nom');
            return $qb->getQuery()->getResult();
        } else {

            $qb = $this->createQueryBuilder('collecte');
            $qb->select('prod.nom  as produit, prod.unite')
                ->addSelect('MAX (collecte.prix) as prix_max')
                ->addSelect('MIN (collecte.prix) as prix_min')
                ->addSelect('SUM(collecte.quantite) as quantite_total')
                ->addSelect('SUM(collecte.quantite_perdu) as quantite_perdu')
                ->addSelect('SUM(collecte.quantite_autre) as quantite_autre')
                ->addSelect('SUM(collecte.quantite_vendu) as quantite_vendu')
                ->addSelect('condi.nom as conditionnement')
                ->where('collecte.isCertified = true')

                ->andWhere($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('zon.nom', ':zone'),
                    $qb->expr()->like('depart.nom', ':departement'),
                    $qb->expr()->like('reg.nom', ':region'),
                    $qb->expr()->like('profil.nom', ':profils'),
                    $qb->expr()->like('prod.nom', ':produits'),
                )))

                // ->leftJoin('collecte.produits', 'prod')
                // ->leftJoin('collecte.unites', 'unit')
                // ->leftJoin('collecte.emballages', 'emb')
                // ->leftJoin('collecte.conditionnements', 'condi')
                ->innerJoin('App\Entity\Produits', 'prod', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.produits = prod')
                ->innerJoin('App\Entity\Unites', 'unit', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unit')
                ->innerJoin('App\Entity\Emballage', 'emb', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.emballages = emb')
                ->innerJoin('App\Entity\Conditionnements', 'condi', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.conditionnements = condi')

                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart')
                ->innerJoin('App\Entity\Profils', 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.profil = profil ')

                ->setParameter('zone', '%' . $zone . '%')
                ->setParameter('departement', '%' . $department . '%')
                ->setParameter('region', '%' . $region . '%')
                ->setParameter('profils', '%' . $profil . '%')
                ->setParameter('produits', '%' . $produit . '%')

                ->distinct('condi.nom')
                ->groupBy('prod.nom')
                ->addGroupBy('condi.nom');

            return $qb->getQuery()->getResult();
        }
    }

    // agregation By Conditionnement:  production grouper par conditionnement
    public function agregationByConditionnement(
        string $produit,
        string $conditionnement,
        string $emballage,
        string $profil,
        string $dateDebut,
        string $dateFin,
        string $region,
        string $department,
        string $zone,
    ) {
        if ($dateDebut != null && $dateFin != null) {
            $dd = new \DateTime($dateDebut);
            $df = new \DateTime($dateFin);

            $qb = $this->createQueryBuilder('collecte');
            $qb->select('prod.nom  as produit , prod.unite as unite')
                ->addSelect('MAX (collecte.prix) as prix_max')
                ->addSelect('MIN (collecte.prix) as prix_min')
                ->addSelect('SUM(collecte.quantite) as quantite_total')
                ->addSelect('SUM(collecte.quantite_perdu) as quantite_perdu')
                ->addSelect('SUM(collecte.quantite_autre) as quantite_autre')
                ->addSelect('SUM(collecte.quantite_vendu) as quantite_vendu')
                ->addSelect('unit.nom as unites')
                ->addSelect('condi.nom as conditionnement')
                ->addSelect('emb.nom as emballage')

                ->where('collecte.isCertified = true')

                ->andWhere($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('zon.nom', ':zone'),
                    $qb->expr()->like('depart.nom', ':departement'),
                    $qb->expr()->like('reg.nom', ':region'),
                    $qb->expr()->like('profil.nom', ':profils'),
                    $qb->expr()->like('prod.nom', ':produits'),
                    $qb->expr()->like('condi.nom', ':conditionnement'),
                    $qb->expr()->like('emb.nom', ':emballage'),
                    $qb->expr()->between('collecte.dateCollecte', ':dateDebut', ':dateFin'),
                )))

                ->innerJoin('App\Entity\Produits', 'prod', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.produits = prod')
                ->innerJoin('App\Entity\Unites', 'unit', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unit')
                ->innerJoin('App\Entity\Emballage', 'emb', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.emballages = emb')
                ->innerJoin('App\Entity\Conditionnements', 'condi', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.conditionnements = condi')

                // ->leftJoin('collecte.produits', 'prod')
                // ->leftJoin('collecte.unites', 'unit')
                // ->leftJoin('collecte.emballages', 'emb')
                // ->leftJoin('collecte.conditionnements', 'condi')

                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart')
                ->innerJoin('App\Entity\Profils', 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.profil = profil ')

                ->setParameter('dateDebut', $dd)
                ->setParameter('dateFin', $df)
                ->setParameter('zone', '%' . $zone . '%')
                ->setParameter('departement', '%' . $department . '%')
                ->setParameter('region', '%' . $region . '%')
                ->setParameter('profils', '%' . $profil . '%')
                ->setParameter('conditionnement', '%' . $conditionnement . '%')
                ->setParameter('produits', '%' . $produit . '%')
                ->setParameter('emballage', '%' . $emballage . '%')

                ->groupBy('condi.nom')
                ->addGroupBy('prod.nom');


            return $qb->getQuery()->getResult();
        } else {

            $qb = $this->createQueryBuilder('collecte');
            $qb->select('prod.nom  as produit , prod.unite as unite')
                ->addSelect('MAX (collecte.prix) as prix_max')
                ->addSelect('MIN (collecte.prix) as prix_min')
                ->addSelect('SUM(collecte.quantite) as quantite_total')
                ->addSelect('SUM(collecte.quantite_perdu) as quantite_perdu')
                ->addSelect('SUM(collecte.quantite_autre) as quantite_autre')
                ->addSelect('SUM(collecte.quantite_vendu) as quantite_vendu')
                ->addSelect('unit.nom as unites')
                ->addSelect('condi.nom as conditionnement')
                ->addSelect('emb.nom as emballage')

                ->where('collecte.isCertified = true')

                ->andWhere($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('zon.nom', ':zone'),
                    $qb->expr()->like('depart.nom', ':departement'),
                    $qb->expr()->like('reg.nom', ':region'),
                    $qb->expr()->like('profil.nom', ':profils'),
                    $qb->expr()->like('prod.nom', ':produits'),
                    $qb->expr()->like('condi.nom', ':conditionnement'),

                    $qb->expr()->like('emb.nom', ':emballage'),
                )))

                ->innerJoin('App\Entity\Produits', 'prod', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.produits = prod')
                ->innerJoin('App\Entity\Unites', 'unit', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unit')
                ->innerJoin('App\Entity\Emballage', 'emb', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.emballages = emb')
                ->innerJoin('App\Entity\Conditionnements', 'condi', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.conditionnements = condi')


                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart')
                ->innerJoin('App\Entity\Profils', 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.profil = profil ')


                ->setParameter('zone', '%' . $zone . '%')
                ->setParameter('departement', '%' . $department . '%')
                ->setParameter('region', '%' . $region . '%')
                ->setParameter('profils', '%' . $profil . '%')
                ->setParameter('conditionnement', '%' . $conditionnement . '%')
                ->setParameter('produits', '%' . $produit . '%')
                ->setParameter('emballage', '%' . $emballage . '%')

                ->groupBy('condi.nom')
                ->addGroupBy('prod.nom');

            return $qb->getQuery()->getResult();
        }
    }


    // agregation By Conditionnement:  production grouper par conditionnement
    public function agregationByProfil(
        string $produit,
        string $conditionnement,
        string $emballage,
        string $profil,
        string $dateDebut,
        string $dateFin,
        string $region,
        string $department,
        string $zone,
    ) {

        if ($dateDebut != '' && $dateFin != '') {

            $qb = $this->createQueryBuilder('collecte');
            $qb->select('prod.nom  as produit , prod.unite as unite')
                ->addSelect('MAX (collecte.prix) as prix_max')
                ->addSelect('MIN (collecte.prix) as prix_min')
                ->addSelect('SUM(collecte.quantite) as quantite_total')
                ->addSelect('SUM(collecte.quantite_perdu) as quantite_perdu')
                ->addSelect('SUM(collecte.quantite_autre) as quantite_autre')
                ->addSelect('SUM(collecte.quantite_vendu) as quantite_vendu')
                ->addSelect('profil.nom as profils')
                ->addSelect('condi.nom as conditionnement')
                ->where('collecte.isCertified = true')

                ->andWhere($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('zon.nom', ':zone'),
                    $qb->expr()->like('depart.nom', ':departement'),
                    $qb->expr()->like('reg.nom', ':region'),
                    $qb->expr()->like('profil.nom', ':profils'),
                    $qb->expr()->like('prod.nom', ':produits'),
                    $qb->expr()->like('condi.nom', ':conditionnement'),
                    $qb->expr()->like('emb.nom', ':emballage'),
                    $qb->expr()->between('collecte.dateCollecte', ':dateDebut', ':dateFin'),
                )))

                ->leftJoin('collecte.produits', 'prod')
                ->leftJoin('collecte.unites', 'unit')
                ->leftJoin('collecte.emballages', 'emb')
                ->leftJoin('collecte.conditionnements', 'condi')

                // ->innerJoin('App\Entity\Produits', 'prod', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.produits = prod')
                // ->innerJoin('App\Entity\Unites', 'unit', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unit')
                // ->innerJoin('App\Entity\Emballage', 'emb', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.emballages = emb')
                // ->innerJoin('App\Entity\Conditionnements', 'condi', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.conditionnements = condi')

                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart')
                ->innerJoin('App\Entity\Profils', 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.profil = profil ')


                ->setParameter('zone', '%' . $zone . '%')
                ->setParameter('departement', '%' . $department . '%')
                ->setParameter('region', '%' . $region . '%')
                ->setParameter('profils', '%' . $profil . '%')
                ->setParameter('conditionnement', '%' . $conditionnement . '%')
                ->setParameter('produits', '%' . $produit . '%')
                ->setParameter('emballage', '%' . $emballage . '%')
                ->setParameter('dateDebut', new \DateTime($dateDebut))
                ->setParameter('dateFin',  new \DateTime($dateFin))

                ->groupBy('profil.nom')
                ->addGroupBy('condi.nom')
                ->distinct('condi.nom');

            return $qb->getQuery()->getResult();
        } else {

            $qb = $this->createQueryBuilder('collecte');
            $qb->select('prod.nom  as produit , prod.unite as unite')
                ->addSelect('MAX (collecte.prix) as prix_max')
                ->addSelect('MIN (collecte.prix) as prix_min')
                ->addSelect('SUM(collecte.quantite) as quantite_total')
                ->addSelect('SUM(collecte.quantite_perdu) as quantite_perdu')
                ->addSelect('SUM(collecte.quantite_autre) as quantite_autre')
                ->addSelect('SUM(collecte.quantite_vendu) as quantite_vendu')
                ->addSelect('profil.nom as profils')
                ->addSelect('condi.nom as conditionnement')
                ->where('collecte.isCertified = true')

                ->andWhere($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('zon.nom', ':zone'),
                    $qb->expr()->like('depart.nom', ':departement'),
                    $qb->expr()->like('reg.nom', ':region'),
                    $qb->expr()->like('profil.nom', ':profils'),
                    $qb->expr()->like('prod.nom', ':produits'),
                    $qb->expr()->like('condi.nom', ':conditionnement'),
                    $qb->expr()->like('emb.nom', ':emballage'),
                )))

                // ->leftJoin('collecte.produits', 'prod')
                // ->leftJoin('collecte.unites', 'unit')
                // ->leftJoin('collecte.emballages', 'emb')
                // ->leftJoin('collecte.conditionnements', 'condi')
                ->innerJoin('App\Entity\Produits', 'prod', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.produits = prod')
                ->innerJoin('App\Entity\Unites', 'unit', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unit')
                ->innerJoin('App\Entity\Emballage', 'emb', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.emballages = emb')
                ->innerJoin('App\Entity\Conditionnements', 'condi', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.conditionnements = condi')


                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart')
                ->innerJoin('App\Entity\Profils', 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.profil = profil ')


                ->setParameter('zone', '%' . $zone . '%')
                ->setParameter('departement', '%' . $department . '%')
                ->setParameter('region', '%' . $region . '%')
                ->setParameter('profils', '%' . $profil . '%')
                ->setParameter('conditionnement', '%' . $conditionnement . '%')
                ->setParameter('produits', '%' . $produit . '%')
                ->setParameter('emballage', '%' . $emballage . '%')

                ->groupBy('profil.nom')
                ->addGroupBy('condi.nom')
                ->distinct('condi.nom');

            return $qb->getQuery()->getResult();
        }
    }



    //productions
    public function getPCollecte(
        string $produit,
        string $conditionnement,
        string $emballage,
        string $profil,
        string $dateDebut,
        string $dateFin,
        string $region,
        string $department,
        string $zone,
    ) {

        if ($dateDebut != null && $dateFin != null) {
            $dd = new \DateTime($dateDebut);
            $df = new \DateTime($dateFin);

            $qb = $this->createQueryBuilder('collecte');


            $qb->select('prod.nom  as produit, prod.unite')
                ->addSelect('MAX (collecte.prix) as prix_max')
                ->addSelect('MIN (collecte.prix) as prix_min')
                ->addSelect('unit.nom as unites')
                ->addSelect('emb.nom as emballage')
                ->addSelect('condi.nom as conditionnement')
                ->addSelect('SUM(collecte.quantite) as quantite_total')
                ->addSelect('SUM(collecte.quantite_perdu) as quantite_perdu')
                ->addSelect('SUM(collecte.quantite_autre) as quantite_autre')
                ->addSelect('SUM(collecte.quantite_vendu) as quantite_vendu')
                ->where('collecte.isCertified = true')


                ->where('collecte.isCertified = true')
                ->andWhere($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('zon.nom', ':zone'),
                    $qb->expr()->like('depart.nom', ':departement'),
                    $qb->expr()->like('reg.nom', ':region'),
                    $qb->expr()->like('profil.nom', ':profil'),
                    $qb->expr()->like('condi.nom', ':conditionnement'),
                    $qb->expr()->like('prod.nom', ':produit'),
                    $qb->expr()->like('emb.nom', ':emballage'),
                    $qb->expr()->between('collecte.dateCollecte', ':dateDebut', ':dateFin'),
                )))


                ->leftJoin('collecte.produits', 'prod')
                ->leftJoin('collecte.unites', 'unit')
                ->leftJoin('collecte.emballages', 'emb')
                ->leftJoin('collecte.conditionnements', 'condi')

                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart')
                ->innerJoin('App\Entity\Profils', 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.profil = profil ')


                ->setParameter('dateDebut', $dd)
                ->setParameter('dateFin', $df)
                ->setParameter('zone', '%' . $zone . '%')
                ->setParameter('departement', '%' . $department . '%')
                ->setParameter('region', '%' . $region . '%')
                ->setParameter('profil', '%' . $profil . '%')
                ->setParameter('conditionnement', '%' . $conditionnement . '%')
                ->setParameter('produit', '%' . $produit . '%')
                ->setParameter('emballage', '%' . $emballage . '%')


                ->groupBy('prod.nom')
                ->addGroupBy('condi.nom')
                ->orderBy('prod.id', 'DESC');

            return $qb->getQuery()->getResult();
        } else {

            $qb = $this->createQueryBuilder('collecte');

            $qb->select('prod.nom  as produit, prod.unite')
                ->addSelect('MAX (collecte.prix) as prix_max')
                ->addSelect('MIN (collecte.prix) as prix_min')
                ->addSelect('unit.nom as unites')
                ->addSelect('emb.nom as emballage')
                ->addSelect('condi.nom as conditionnement')
                ->addSelect('SUM(collecte.quantite) as quantite_total')
                ->addSelect('SUM(collecte.quantite_perdu) as quantite_perdu')
                ->addSelect('SUM(collecte.quantite_autre) as quantite_autre')
                ->addSelect('SUM(collecte.quantite_vendu) as quantite_vendu')
                ->where('collecte.isCertified = true')


                ->where('collecte.isCertified = true')
                ->andWhere($qb->expr()->andX($qb->expr()->andX(
                    $qb->expr()->like('zon.nom', ':zone'),
                    $qb->expr()->like('depart.nom', ':departement'),
                    $qb->expr()->like('reg.nom', ':region'),
                    $qb->expr()->like('profil.nom', ':profil'),
                    $qb->expr()->like('condi.nom', ':conditionnement'),
                    $qb->expr()->like('prod.nom', ':produit'),
                    $qb->expr()->like('emb.nom', ':emballage'),

                )))


                ->leftJoin('collecte.produits', 'prod')
                ->leftJoin('collecte.unites', 'unit')
                ->leftJoin('collecte.emballages', 'emb')
                ->leftJoin('collecte.conditionnements', 'condi')

                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart')
                ->innerJoin('App\Entity\Profils', 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.profil = profil ')



                ->setParameter('zone', '%' . $zone . '%')
                ->setParameter('departement', '%' . $department . '%')
                ->setParameter('region', '%' . $region . '%')
                ->setParameter('profil', '%' . $profil . '%')
                ->setParameter('conditionnement', '%' . $conditionnement . '%')
                ->setParameter('produit', '%' . $produit . '%')
                ->setParameter('emballage', '%' . $emballage . '%')


                ->groupBy('prod.nom')
                ->addGroupBy('condi.nom')
                ->orderBy('prod.id', 'DESC');

            return $qb->getQuery()->getResult();
        }
    }
}