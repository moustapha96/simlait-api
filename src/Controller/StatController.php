<?php


namespace App\Controller;

use App\Entity\Departement;
use App\Repository\CollecteRepository;
use App\Repository\ConditionnementsRepository;
use App\Repository\DepartementRepository;
use App\Repository\EmballageRepository;
use App\Repository\ProduitsRepository;
use App\Repository\RegionRepository;
use App\Repository\UnitesRepository;
use App\Repository\ZonesRepository;
use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\EntityManagerInterface;
use App\Doctrine\ReopeningEntityManagerInterface;
use Doctrine\ORM\EntityManagerInterface as ORMEntityManagerInterface;
use DoctrineExtensions\Query\Mysql\Date;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Node\ConditionalNode;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class StatController extends AbstractController
{
    public function __construct()
    {
    }

    //nbre de collecte par profil
    /**
     * @Route("/api/getCollecteByProfil", name="app_collecte_profil",methods={"GET"})
     */
    public function getCollecteByProfil(CollecteRepository $collecteRepository): Response
    {

        try {
            $unites = $collecteRepository->groupCollectebyProfil();

            $resultats = array();
            foreach ($unites as $m) {
                $zone = array(
                    'name' => $m['name'],
                    'value' => $m['nbre']
                );
                $resultats[] = $zone;
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    //quantite collecter 

    /**
     * @Route("/api/getCollecteQuantiteByDate", name="app_collecte_quantite", methods ={"GET"})
     */
    public function getCollecteQuantiteByDate(CollecteRepository $collecteRepository): Response
    {

        try {
            $res = $collecteRepository->getCollecteQuantiteByDate();
            $resultats = [];

            foreach ($res as $value) {

                if ($this->isExite($value['produit'], $resultats) == false) {
                    $resultats[] = ['name' => $value['produit'], 'series' => $this->getSerie($value['produit'], $res)];
                }
            }

            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    // existance element
    public function isExite(string $nom, array $tableau)
    {

        foreach ($tableau as $v) {
            if ($v['name'] == $nom) {
                return true;
            }
        }
        return false;
    }

    //fonction return series
    public function getSerie(string $nom, array $tableau)
    {
        $resultats = [];
        foreach ($tableau as $value) {
            if ($nom == $value['produit']) {
                $resultats[] = ['value' => $value['quantiteTotal'], 'name' => $value['date']];
            }
        }
        return $resultats;
    }

    // le produits le plus collecter
    /**
     * @Route("/api/getProduitlePlusCollectes", name="app_produit_plus_co", methods={"GET"} )
     */
    public function getProduitlePlusCollectes(CollecteRepository $repo): Response
    {
        try {
            $res = $repo->getProduitlePlusCollectes();

            $produit =  '';
            $max = 0;

            foreach ($res as $val) {
                $current = $val['nbre'];
                $produit = $val['produit'];
                $max = $current > $max ? $current : $max;
            };

            return new JsonResponse(['produit' => $produit, 'nbre' => $max], 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }


    // nbre collecte Mois actuel sur mois passé
    /**
     * @Route("/api/getCollecteMoisActuelPasse", name="app_collecte_mois_actule_passer",methods={"GET"})
     */
    public function getCollecteMoisActuelPasse(CollecteRepository $collecteRepository): Response
    {

        try {
            $resNow = $collecteRepository->getCollecteNowMonth();
            $resP = $collecteRepository->getCollectepreviousMonth();

            return new JsonResponse(['now' => $resNow[0]['nbre'], 'previous' => $resP[0]['nbre']], 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    //le type d'unite le plus collecter 
    // nbre collecte Mois actuel sur mois passé
    /**
     * @Route("/api/getUnitesMoisActuelPasse", name="app_unites_mois_actule_passer",methods={"GET"})
     */
    public function getUnitesMoisActuelPasse(CollecteRepository $collecteRepository): Response
    {
        try {
            $resNow = $collecteRepository->getUniteNowMonth();
            $resP = $collecteRepository->getUnitepreviousMonth();

            return new JsonResponse(['now' => $resNow[0]['nbre'], 'previous' => $resP[0]['nbre']], 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    // le produit le plus collecter

    /**
     * @Route("/api/getProduitPlusCollecterMois", name="app_produit_plus_collecter_mois" , methods={"GET"})
     */
    public function getProduitPlusCollecterMois(CollecteRepository $collecteRepository): Response
    {


        try {

            $resN = $collecteRepository->getProduitPlusCollecterNow();
            $resP = $collecteRepository->getProduitPlusCollectePrevious();

            $maxNow =  0;
            $maxPr = 0;
            $maxNowProfil = '';
            $maxPreviousProfil = '';

            foreach ($resN as $val) {
                $current = $val['nbre'];
                $maxNowProfil = $val['profil'];
                $maxNow = $current > $maxNow ? $current : $maxNow;
            };
            foreach ($resP as $val) {
                $current = $val['nbre'];
                $maxPreviousProfil = $val['profil'];
                $maxPr = $current > $maxPr ? $current : $maxPr;
            };

            return new JsonResponse([
                'profilNow' => $maxNowProfil, 'maxNow' => $maxNow,
                'profilPrevious' => $maxPreviousProfil, 'maxPrevious' => $maxPr
            ], 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    // nbre unite par zone 
    /**
     * @Route("/api/getUniteSByZone", name="app_unite_zone",methods={"GET"})
     */
    public function getUnitesByZone(UnitesRepository $unitesRepository): Response
    {
        try {
            $unites = $unitesRepository->groupUnitesbyZone();

            $resultats = array();
            foreach ($unites as $m) {
                $zone = array(
                    'name' => $m['name'],
                    'value' => $m['nbre']
                );
                $resultats[] = $zone;
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    // 10 dernier collectes
    /**
     * @Route("/api/getLastCollectes", name="app_last_collecte",methods={"GET" , "POST"})
     * @param Request $request
     */
    public function getLast(Request $request, CollecteRepository $repo): Response
    {
        try {
            if ($request->getMethod() == "GET") {
                $collectes = $repo->findLast('', '');
            } else if ($request->getMethod() == "POST") {
                $data = json_decode($request->getContent(), true);
                $dateDebut = $data['dateDebut'];
                $dateFin = $data['dateFin'];
                $collectes = $repo->findLast($dateDebut, $dateFin);
            } else {
                return new JsonResponse([], 200, ["Content-Type" => "application/json"]);
            }


            $resultats = array();
            foreach ($collectes as $m) {
                dump($m);
                $resultats[] = $m->asArray();
            }
            // dd();
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    // collecte certifier 
    /**
     * @Route("/api/getCollectesCertified", name="app_getCollectesCertified_collecte",methods={"GET" , "POST"})
     * @param Request $request
     */
    public function getCollectesCertified(Request $request, CollecteRepository $repo): Response
    {
        try {
            if ($request->getMethod() == "GET") {
                $collectes = $repo->findCertified('', '');
            } else if ($request->getMethod() == "POST") {
                $data = json_decode($request->getContent(), true);
                $dateDebut = $data['dateDebut'];
                $dateFin = $data['dateFin'];
                $collectes = $repo->findCertified($dateDebut, $dateFin);
            } else {
                return new JsonResponse([], 200, ["Content-Type" => "application/json"]);
            }

            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    // collecte by zone
    /**
     * @Route("/api/getCollecteByZone", name="app_getCollecteByZone_collecte",methods={"GET" , "POST"} )
     * @param Request $request
     */
    public function getCollecteByZone(Request $request, CollecteRepository $repo): Response
    {
        try {

            if ($request->getMethod() == "GET") {
                $collectes = $repo->groupCollecteByZone('', '');
            } else if ($request->getMethod() == "POST") {
                $data = json_decode($request->getContent(), true);
                $dateDebut = $data['dateDebut'];
                $dateFin = $data['dateFin'];
                $collectes = $repo->groupCollecteByZone($dateDebut, $dateFin);
            } else {
                return new JsonResponse([], 200, ["Content-Type" => "application/json"]);
            }
            $resultats = array();
            foreach ($collectes as $m) {
                $zone = array(
                    'name' => $m['name'],
                    'value' => $m['value']
                );
                $resultats[] = $zone;
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }


    // le produits le plus collecter
    /**
     * @Route("/api/getProduitPlusCollecter", name="app_produit_plus_collecter", methods={"GET", "POST"} )
     */
    public function getProduitPlusCollecter(Request $request, CollecteRepository $repo): Response
    {
        try {
            if ($request->getMethod() == "GET") {
                $collectes = $repo->getProduitPlusCollecter('', '');
            } else if ($request->getMethod() == "POST") {
                $data = json_decode($request->getContent(), true);
                $dateDebut = $data['dateDebut'];
                $dateFin = $data['dateFin'];
                $collectes = $repo->getProduitPlusCollecter($dateDebut, $dateFin);
            } else {
                return new JsonResponse([], 200, ["Content-Type" => "application/json"]);
            }
            $resultats = array();

            foreach ($collectes as $m) {
                $zone = array(
                    'name' => $m['name'],
                    'value' => $m['value']
                );
                $resultats[] = $zone;
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }


    // collecte par date
    /**
     * @Route("/api/getCollecteByDate", name="app_getCollecteByDate_collecte", methods={"GET", "POST"} )
     * @param Request $request
     */
    public function groupCollecteByDate(Request $request, CollecteRepository $repo): Response
    {
        try {
            if ($request->getMethod() == "GET") {
                $collectes = $repo->groupCollecteByDate('', '');
            } else if ($request->getMethod() == "POST") {
                $data = json_decode($request->getContent(), true);
                $dateDebut = $data['dateDebut'];
                $dateFin = $data['dateFin'];
                $collectes = $repo->groupCollecteByDate($dateDebut, $dateFin);
            } else {
                return new JsonResponse([], 200, ["Content-Type" => "application/json"]);
            }

            $resultats = array();

            foreach ($collectes as $m) {
                $zone = array(
                    'name' => $m['name'],
                    'value' => $m['value']
                );
                $resultats[] = $zone;
            }


            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    //production grouper par conditionnement
    /**
     * @Route("/api/agregations/ByConditionnement", name="app_agregations_by_conditionnement_collecte", methods={"GET", "POST"} )
     * @param Request $request
     */
    public function agregationByConditionnement(Request $request, CollecteRepository $proRepo): Response
    {
        try {
            if ($request->getMethod() == "GET") {
                $res_p_c = $proRepo->agregationByConditionnement('', '', '', '', '', '', '', '', '');
            } else if ($request->getMethod() == "POST") {

                $data = json_decode($request->getContent(), true);
                $dateDebut = $data['dateDebut'];
                $dateFin = $data['dateFin'];
                $region = $data['region'];
                $department = $data['departement'];
                $zone = $data['zone'];
                $profil = $data['profil'];
                $produit = $data['produit'];
                $conditionnement = $data['conditionnement'];

                $emballage = $data['emballage'];

                $res_p_c = $proRepo->agregationByConditionnement(
                    $produit,
                    $conditionnement,
                    $emballage,
                    $profil,
                    $dateDebut,
                    $dateFin,
                    $region,
                    $department,
                    $zone
                );
            } else {
                return new JsonResponse([], 200, ["Content-Type" => "application/json"]);
            }


            return new JsonResponse($res_p_c, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    //production grouper par produit
    /**
     * @Route("/api/agregations/ByProduit", name="app_agregations_by_produit_collecte", methods={"GET", "POST"} )
     */
    public function agregationByProduit(Request $request, CollecteRepository $proRepo): Response
    {
        try {
            if ($request->getMethod() == "GET") {
                $res_p_c = $proRepo->agregationByProduit('', '', '', '', '', '', '', '', '');
            } else if ($request->getMethod() == "POST") {
                $data = json_decode($request->getContent(), true);
                $dateDebut = $data['dateDebut'];
                $dateFin = $data['dateFin'];
                $region = $data['region'];
                $department = $data['departement'];
                $zone = $data['zone'];
                $profil = $data['profil'];
                $produit = $data['produit'];
                $conditionnement = $data['conditionnement'];

                $emballage = $data['emballage'];

                $res_p_c = $proRepo->agregationByProduit(
                    $produit,
                    $conditionnement,
                    $emballage,
                    $profil,
                    $dateDebut,
                    $dateFin,
                    $region,
                    $department,
                    $zone
                );
            } else {
                return new JsonResponse([], 200, ["Content-Type" => "application/json"]);
            }
            return new JsonResponse($res_p_c, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    //production grouper par produit
    /**
     * @Route("/api/agregations/ByProfil", name="app_agregations_by_profil_collecte", methods={"GET", "POST"} )
     */
    public function agregationByProfil(Request $request, CollecteRepository $proRepo): Response
    {
        try {

            if ($request->getMethod() == "GET") {
                $res_p_c = $proRepo->agregationByProfil('', '', '', '', '', '', '', '', '');
                // dd($res_p_c );
            } else if ($request->getMethod() == "POST") {
                $data = json_decode($request->getContent(), true);
                $dateDebut = $data['dateDebut'];
                $dateFin = $data['dateFin'];
                $region = $data['region'];
                $department = $data['departement'];
                $zone = $data['zone'];
                $profil = $data['profil'];
                $produit = $data['produit'];
                $conditionnement = $data['conditionnement'];
                $emballage = $data['emballage'];


                $res_p_c = $proRepo->agregationByProfil(
                    $produit,
                    $conditionnement,
                    $emballage,
                    $profil,
                    $dateDebut,
                    $dateFin,
                    $region,
                    $department,
                    $zone
                );
            } else {
                return new JsonResponse([], 200, ["Content-Type" => "application/json"]);
            }
            return new JsonResponse($res_p_c, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    //agregation find produit 
    /**
     * @Route("/api/agregations/findProduitConditionnements",name="app_agregations_produit_conditionnement_collecte", methods={"POST"})
     * @param Request $request
     */
    public function findPrdouitConditionnements(Request $request, CollecteRepository $repo): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $profil = $data['profil'];
            $produit = $data['produit'];
            $conditionnement = $data['conditionnement'];
            $emballage = $data['emballage'];

            $dateDebut = $data['dateDebut'];
            $dateFin = $data['dateFin'];
            $region = $data['region'];
            $departement = $data['departement'];
            $zone = $data['zone'];
            $groupBy = $data['groupBy'];

            $collectes = $repo->findPC(
                $produit,
                $conditionnement,
                $emballage,
                $profil,
                $region,
                $departement,
                $zone,
                $groupBy,
                $dateDebut,
                $dateFin
            );

            return new JsonResponse($collectes, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    //production 
    /**
     * @Route("/api/productions", name="app_production_collecte", methods={"GET", "POST"} )
     * @param Request $request
     */
    public function production(Request $request, ProduitsRepository $proRepo): Response
    {
        try {
            if ($request->getMethod() == "GET") {
                $res_p_c = $proRepo->getPCollecte('', '', '', '', '', '', '', '', '');
                // dd($res_p_c );
            } else if ($request->getMethod() == "POST") {
                $data = json_decode($request->getContent(), true);
                $dateDebut = $data['dateDebut'];
                $dateFin = $data['dateFin'];
                $region = $data['region'];
                $department = $data['departement'];
                $zone = $data['zone'];
                $profil = $data['profil'];
                $produit = $data['produit'];
                $conditionnement = $data['conditionnement'];
                $emballage = $data['emballage'];

                $res_p_c = $proRepo->getPCollecte(
                    $produit,
                    $conditionnement,
                    $emballage,
                    $profil,
                    $dateDebut,
                    $dateFin,
                    $region,
                    $department,
                    $zone
                );
            } else {
                return new JsonResponse([], 200, ["Content-Type" => "application/json"]);
            }
            $resultats = array();

            $produits = $proRepo->findAll();
            foreach ($res_p_c as $m) {
                if (get_class($m[0]) == "App\Entity\Collecte") {
                    $coll = array(
                        'conditionnement' => $m['conditionnement'],
                        'produit' => $m['produit'],
                        'quantite_total' => $m['quantite_total'],
                        'prix_max' => $m['prix_max'],
                        'prix_min' => $m['prix_min'],
                        "unites" => $m["unites"],
                        "emballage" => $m["emballage"],
                        "quantite_perdu" => $m['quantite_perdu'],
                        "quantite_autre" => $m['quantite_autre'],
                        "quantite_vendu" => $m['quantite_vendu'],
                    );
                    $resultats[] = $coll;
                }
            }
            foreach ($produits as $pro) {
                $tmp = false;
                foreach ($resultats as $res) {
                    if (in_array($pro->getNom(), $res)) {
                        $tmp = true;
                    }
                }
                if ($tmp == false) {
                    $pcondi = $proRepo->getCondi($pro->getNom());

                    foreach ($pcondi[0]->getConditionnements() as $c) {

                        $coll = array(
                            'conditionnement' => $c->getNom(),
                            'produit' => $pro->getNom(),
                            'quantite_total' => 0,
                            'prix_max' => 0,
                            'prix_min' => 0,
                            "unites" => 'ND',
                            "emballage" => 'ND',
                            "quantite_perdu" => 0,
                            "quantite_vendu" => 0,
                            "quantite_autre" => 0,
                        );
                        $resultats[] = $coll;
                    }
                } else if ($tmp == true) {
                    $pcondi = $proRepo->getCondi($pro->getNom())[0]->getConditionnements();
                    foreach ($pcondi as $c) {
                        $tmpC = false;
                        foreach ($resultats as $res) {
                            if (in_array($c->getNom(), $res) && in_array($pro->getNom(), $res)) {
                                $tmpC = true;
                            }
                        }
                        if ($tmpC == false) {
                            $coll = array(
                                'conditionnement' => $c->getNom(),
                                'produit' => $pro->getNom(),
                                'quantite_total' => 0,
                                'prix_max' => 0,
                                'prix_min' => 0,
                                "unites" => 'ND',
                                "emballage" => 'ND',
                                "quantite_perdu" => 0,
                                "quantite_vendu" => 0,
                                "quantite_autre" => 0,
                            );
                            $resultats[] = $coll;
                        }
                    }
                }
            }

            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }


    /**
     * @Route("/api/productions/findPC", name="app_mer_findPC_collecte",methods={"POST"})
     * @param Request $request
     */
    public function findPC(Request $request, ProduitsRepository $repo): ?Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $produit = $data['produit'];
            $conditionnement = $data['conditionnement'];
            $emballage = $data['emballage'];
            $collectes = $repo->findPC($produit, $conditionnement, '');

            $resultats = array();
            foreach ($collectes as $m) {
                // dump($m[0]);
                if (get_class($m[0]) == "App\Entity\Collecte") {
                    $coll = array(
                        'conditionnement' => $m['conditionnement'],
                        'produit' => $m['produit'],
                        'unites' => $m['unites'],
                        'region' => $m['region'],
                        'prix' => $m['prix'],
                        'zone' => $m['zone'],
                        'departement' => $m['departement'],
                        'quantite' => $m['quantite'],
                        'emballage' => $m['emballage'],
                        "quantite_perdu" => $m['quantite_perdu'],
                        "quantite_autre" => $m['quantite_autre'],
                        "quantite_vendu" => $m['quantite_vendu'],
                    );
                    $resultats[] = $coll;
                }
                // $resultats[] = $coll;
            }

            // dd($resultats);
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    /**
     *@Route("/api/productions/details", name="api_pc_detail", methods={"POST"})
     * @param Request $request
     */
    public function findPCDetails(Request $request, ProduitsRepository $repo): ?Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $produit = $data['produit'];
            $conditionnement = $data['conditionnement'];

            $resultats = $repo->getPCollecteDetail($produit, $conditionnement);

            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Throwable $th) {
            return new JsonResponse([$th], 200, ["Content-Type" => "application/json"]);
        }
    }

    /**
     * @Route("/api/productions/searchPC", name="app_mer_findPC_search",methods={"POST"})
     * @param Request $request
     */
    public function searchPC(Request $request, CollecteRepository $repo): ?Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            $produit = $data['produit'];
            $conditionnement = $data['conditionnement'];
            $region = $data['region'];
            $departement = $data['departement'];
            $dateDebut = $data['dateDebut'];
            $dateFin = $data['dateFin'];
            $zone = $data['zone'];

            $collectes = $repo->searchPC($produit, $conditionnement, $region, $departement, $zone, $dateDebut, $dateFin);

            $resultats = array();
            foreach ($collectes as $m) {

                $coll = array(
                    'conditionnement' => $m['conditionnement'],
                    'produit' => $m['produit'],
                    'laiterie' => $m['laiterie'],
                    'region' => $m['region'],
                    'prix' => $m['prix'],
                    'zone' => $m['zone'],
                    'departement' => $m['departement'],
                    'quantite' => $m['quantite'],
                    'emballage' => $m['emballage']
                );
                $resultats[] = $coll;
            }


            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/api/getLowPrice", name="app_low_price", methods={"GET"} )
     */
    public function getLowPrice(CollecteRepository $repo): Response
    {
        try {

            $prix = $repo->getLowPrice();

            $resultats = array();
            foreach ($prix as $m) {

                $coll = array(
                    'prix' => $m['prix'],
                );
                $resultats = $coll;
            }
            // dd($resultats);
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }


    /**
     * @Route("/api/getStatMobile", name="app_stat_mobile", methods={"GET"} )
     */
    public function getStatDRZPE(RegionRepository $rr, DepartementRepository $dr, ProduitsRepository $pr, ZonesRepository $zr, EmballageRepository $er, ConditionnementsRepository $cr): ?Response
    {

        $table = [
            ['table' => "Departement", 'nombre' =>  count($dr->findAll())],
            ['table' => "Region", 'nombre' =>  count($rr->findAll())],
            ['table' => "Conditionnement", 'nombre' =>  count($cr->findAll())],
            ['table' => "Produit", 'nombre' =>  count($pr->findAll())],
            ['table' => "Zone", 'nombre' =>  count($zr->findAll())],
            ['table' => "Emballage", 'nombre' =>  count($er->findAll())],
        ];

        return new JsonResponse($table, 200);
    }
}
