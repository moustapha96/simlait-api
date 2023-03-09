<?php

namespace App\Repository;

use App\Entity\Produits;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Collecte;
use DateTime;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use PhpParser\Node\Expr\FuncCall;
use Symfony\Bridge\Twig\Node\DumpNode;

/**
 * @method Produits|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produits|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produits[]    findAll()
 * @method Produits[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produits::class);
    }

    // /**
    //  * @return Produits[] Returns an array of Produits objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Produits
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    public function getPNoCollecte()
    {
        $qb = $this->createQueryBuilder('prod');
        $qb->innerJoin('App\Entity\Collecte', 'collecte',  \Doctrine\ORM\Query\Expr\Join::WITH, 'produit.id <> collecte.produits');
        $qb
            ->addSelect('lait')->join('nocollecte.unites', 'lait')
            ->addSelect('lait.nom as laiterie')
            ->addSelect('emb')->join('collecte.emballages', 'emb')
            ->addSelect('emb.nom as emballage')
            ->addSelect('nocollecte')
            ->addSelect('prod.nom as produit')
            ->addSelect('MAX (nocollecte.prix) as prix_max')
            ->addSelect('MIN (nocollecte.prix) as prix_min')
            ->addSelect('SUM(nocollecte.quantite) as quantite_total')
            // ->where('collecte.isCertified = true')
            ->groupBy('prod.nom')

            ->orderBy('prod.id', 'DESC');
        return $qb->getQuery()->getResult();
    }

    public function getPCollecteDetail(string $produit, string $conditionnement)
    {

        $qb = $this->createQueryBuilder('prod');
        $qb->innerJoin('App\Entity\Collecte', 'collecte',  \Doctrine\ORM\Query\Expr\Join::WITH, 'prod.id = collecte.produits');
        $qb
            ->innerJoin('App\Entity\Unites', 'unit', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unit')
            ->innerJoin('App\Entity\Emballage', 'emb', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.emballages = emb')
            ->innerJoin('App\Entity\Conditionnements', 'condi', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.conditionnements = condi')

            ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
            ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
            ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart')
            ->innerJoin('App\Entity\Profils', 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.profil = profil ')

            ->select('unit.nom as unites')
            ->addSelect('condi.nom as conditionnement')
            ->addSelect('emb.nom as emballage')
            ->addSelect('collecte')
            ->addSelect('prod.nom as produit')
            ->addSelect('MAX (collecte.prix) as prix_max')
            ->addSelect('MIN (collecte.prix) as prix_min')
            ->addSelect('MIN (collecte.prix) as prix_min')
            ->addSelect('collecte.quantite as quantite')
            ->addSelect('collecte.quantite_perdu as quantite_perdu')
            ->addSelect('collecte.quantite_autre as quantite_autre')
            ->addSelect('ccollecte.quantite_vendu as quantite_vendu')
            ->where('collecte.isCertified = true')
            ->andWhere('condi.nom = :conditionnement')
            ->andWhere('prod.nom = :produit')

            ->setParameter('conditionnement',  $conditionnement)
            ->setParameter('produit', $produit)
            ->orderBy('prod.id', 'DESC');
        return $qb->getQuery()->getResult();
    }


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

            $qb = $this->createQueryBuilder('prod');
            $qb->innerJoin('App\Entity\Collecte', 'collecte',  \Doctrine\ORM\Query\Expr\Join::WITH, 'prod.id = collecte.produits');
            $qb

                // ->leftJoin('collecte.produits', 'prod')
                // ->leftJoin('collecte.unites', 'unit')
                // ->leftJoin('collecte.emballages', 'emb')
                // ->leftJoin('collecte.conditionnements', 'condi')

                // ->innerJoin('App\Entity\Produits', 'prod', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.produits = prod')
                ->innerJoin('App\Entity\Unites', 'unit', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unit')
                ->innerJoin('App\Entity\Emballage', 'emb', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.emballages = emb')
                ->innerJoin('App\Entity\Conditionnements', 'condi', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.conditionnements = condi')


                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart')
                ->innerJoin('App\Entity\Profils', 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.profil = profil ')


                ->select('unit.nom as unites')
                ->addSelect('condi.nom as conditionnement')
                ->addSelect('emb.nom as emballage')
                ->addSelect('collecte')
                ->addSelect('prod.nom as produit')
                ->addSelect('MAX (collecte.prix) as prix_max')
                ->addSelect('MIN (collecte.prix) as prix_min')
                ->addSelect('SUM(collecte.quantite) as quantite_total')
                ->addSelect('SUM(collecte.quantite_perdu) as quantite_perdu')
                ->addSelect('SUM(collecte.quantite_autre) as quantite_autre')
                ->addSelect('SUM(collecte.quantite_vendu) as quantite_vendu')

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

            $qb = $this->createQueryBuilder('prod');
            $qb->innerJoin('App\Entity\Collecte', 'collecte',  \Doctrine\ORM\Query\Expr\Join::WITH, 'prod.id = collecte.produits');
            $qb
                // ->leftJoin('collecte.unites', 'unit')
                // ->leftJoin('collecte.emballages', 'emb')
                // ->leftJoin('collecte.conditionnements', 'condi')

                // ->innerJoin('App\Entity\Produits', 'prod', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.produits = prod')
                ->innerJoin('App\Entity\Unites', 'unit', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.unites = unit')
                ->innerJoin('App\Entity\Emballage', 'emb', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.emballages = emb')
                ->innerJoin('App\Entity\Conditionnements', 'condi', \Doctrine\ORM\Query\Expr\Join::WITH, 'collecte.conditionnements = condi')


                ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
                ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
                ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart')
                ->innerJoin('App\Entity\Profils', 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.profil = profil ')

                ->select('unit.nom as unites')
                ->addSelect('condi.nom as conditionnement')
                ->addSelect('emb.nom as emballage')
                ->addSelect('collecte')
                ->addSelect('prod.nom as produit')
                ->addSelect('MAX (collecte.prix) as prix_max')
                ->addSelect('MIN (collecte.prix) as prix_min')
                ->addSelect('SUM(collecte.quantite) as quantite_total')
                ->addSelect('SUM(collecte.quantite_perdu) as quantite_perdu')
                ->addSelect('SUM(collecte.quantite_autre) as quantite_autre')
                ->addSelect('SUM(collecte.quantite_vendu) as quantite_vendu')
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


    public function findPC(string $produit, string $conditionnement, string $emballage)
    {


        $qb = $this->createQueryBuilder('prod');
        $qb->innerJoin('App\Entity\Collecte', 'collecte',  \Doctrine\ORM\Query\Expr\Join::WITH, 'prod.id = collecte.produits');
        $qb
            ->leftJoin('collecte.unites', 'unit')
            ->leftJoin('collecte.emballages', 'emb')
            ->leftJoin('collecte.conditionnements', 'condi')

            ->innerJoin('App\Entity\Region', 'reg', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.region = reg')
            ->innerJoin('App\Entity\Zones', 'zon', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.zone = zon')
            ->innerJoin('App\Entity\Departement', 'depart', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.departement = depart')
            ->innerJoin('App\Entity\Profils', 'profil', \Doctrine\ORM\Query\Expr\Join::WITH, 'unit.profil = profil ')

            ->select('unit.nom as unites')
            ->addSelect('condi.nom as conditionnement')
            ->addSelect('emb.nom as emballage')
            ->addSelect('reg.nom as region')
            ->addSelect('depart.nom as departement')
            ->addSelect('zon.nom as zone')

            ->addSelect('collecte')
            ->addSelect('prod.nom as produit')
            ->addSelect('collecte.prix as prix')
            ->addSelect('collecte.quantite as quantite')
            ->addSelect('collecte.quantite_vendu as quantite_vendu')
            ->addSelect('collecte.quantite_autre as quantite_autre')
            ->addSelect('collecte.quantite_perdu as quantite_perdu')

            ->where('collecte.isCertified = true')

            ->andWhere($qb->expr()->andX($qb->expr()->andX(
                $qb->expr()->like('condi.nom', ':conditionnement'),
                $qb->expr()->like('prod.nom', ':produit'),
            )))
            ->setParameter('conditionnement', '%' . $conditionnement . '%')
            ->setParameter('produit', '%' . $produit . '%')
            ->orderBy('prod.id', 'DESC');
        return $qb->getQuery()->getResult();
    }


    public function getCondi(string $produit)
    {

        $qb = $this->createQueryBuilder('p');
        $qb->leftJoin('p.conditionnements', 'condi')
            ->addSelect('condi as conditionnement')
            ->where('p.nom = :pro')

            ->setParameter('pro', $produit);
        return $qb->getQuery()->getResult();
    }


    public function findProductions()
    {
        // $qb = $this->createQueryBuilder('prod');
        // $qb->innerJoin('App\Entity\Profil', 'profil',  \Doctrine\ORM\Query\Expr\Join::WITH, 'prod.id = profil.produits');

        $qb = $this->createQueryBuilder('p');
        $qb->leftJoin('p.profils', 'profil')

            ->where('profil.nom = :nom')
            ->setParameter('nom', 'PRODUCTEUR');

        return $qb->getQuery()->getResult();
    }

    public function findTransformateur()
    {
        // $qb = $this->createQueryBuilder('prod');
        // $qb->innerJoin('App\Entity\Profil', 'profil',  \Doctrine\ORM\Query\Expr\Join::WITH, 'prod.id = profil.produits');

        $qb = $this->createQueryBuilder('p');
        $qb->leftJoin('p.profils', 'profil')

            ->where('profil.nom = :nom')
            ->setParameter('nom', 'TRANSFORMATEUR');

        return $qb->getQuery()->getResult();
    }
    public function findCollecteur()
    {
        // $qb = $this->createQueryBuilder('prod');
        // $qb->innerJoin('App\Entity\Profil', 'profil',  \Doctrine\ORM\Query\Expr\Join::WITH, 'prod.id = profil.produits');

        $qb = $this->createQueryBuilder('p');
        $qb->leftJoin('p.profils', 'profil')
            ->leftJoin('p.conditionnements', 'condi')
            ->addSelect('condi as conditionnement')

            ->where('profil.nom = :nom')
            ->setParameter('nom', 'COLLECTEUR');

        return $qb->getQuery()->getResult();
    }


    public function getProduitbyProfil(int $idProfil)
    {

        $qb = $this->createQueryBuilder('p');
        //  $qb->innerJoin('App\Entity\Profils', 'profil',  \Doctrine\ORM\Query\Expr\Join::WITH, 'p.profils = profil')

        $qb->leftJoin('p.profils', 'profil')
            ->leftJoin('p.conditionnements', 'condi')
            ->addSelect('condi as conditionnement')
            ->where('profil.id = :idProfil')
            ->setParameter('idProfil', $idProfil);
        return $qb->getQuery()->getResult();
    }
}