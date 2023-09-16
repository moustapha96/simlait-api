<?php


namespace App\Controller;

use App\Repository\CollecteRepository;
use App\Repository\ConditionnementsRepository;
use App\Repository\DepartementRepository;
use App\Repository\EmballageRepository;
use App\Repository\ProduitsRepository;
use App\Repository\RegionRepository;
use App\Repository\SaisonRepository;
use App\Repository\TableCounterRepository;
use App\Repository\UnitesRepository;
use App\Repository\ZonesRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class StatController extends AbstractController
{
    private $serializer;
    public function __construct(SerializerInterface $serializer)
    {

        $this->serializer = $serializer;
    }

    //nbre de collecte par profil
    /**
     * @Route("/api/getCollecteByProfil", name="app_collecte_profil",methods={"GET"})
     */
    public function getCollecteByProfil(CollecteRepository $collecteRepository): Response
    {

        try {
            $unites = $collecteRepository->getProduitByProfil();

            $resultats = array();
            foreach ($unites as $m) {
                $zone = array(
                    'name' => $m['profil'],
                    'value' => $m['nbre']
                );
                $resultats[] = $zone;
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
                    $resultats[] = ['name' => $value['produit'] . " (" . $value['unite'] . ")", 'series' => $this->getSerie($value['produit'], $res)];
                }
            }

            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
            return new JsonResponse(['err' => $e->getMessage()], 400);
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

                $collectes = $repo->findBy(array(), array('id' => 'DESC'), 10);
                $jsonData = $this->serializer->serialize($collectes, 'json');
                return new JsonResponse(json_decode($jsonData, true), 200);
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
                $resultats[] = $m->asArray();
            }

            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 400);
        }
    }


    //stat collecte certifier 
    /**
     * @Route("/api/getStatCollectescertified", name="app_get_stat_collecte",methods={"GET"})
     */
    public function getStatCollecteCertified(CollecteRepository $collecteRepository, TableCounterRepository $tableCounterRepository): JsonResponse
    {
        $collecteCertified = $tableCounterRepository->findOneBy(['name' => 'collecteCertified']) ? $tableCounterRepository->findOneBy(['name' => 'collecteCertified'])->getValue() : 0;
        $collecteNonCertified = $tableCounterRepository->findOneBy(['name' => 'collecteNonCertified']) ? $tableCounterRepository->findOneBy(['name' => 'collecteNonCertified'])->getValue() : 0;
        $collectes = count($collecteRepository->findAll());

        return new JsonResponse([
            'collecteCertified' => $collecteCertified,
            'collectes' => $collectes,
            'collecteNonCertified' => $collecteNonCertified
        ], 200, ["Content-Type" => "application/json"]);
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
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
                        'prix_moyen' => $m['prix_moyen'],
                        "unites" => $m["unites"],
                        "emballage" => $m["emballage"],
                        "quantite_perdu" => $m['quantite_perdu'],
                        "quantite_autre" => $m['quantite_autre'],
                        "quantite_vendu" => $m['quantite_vendu'],
                        "unite" => $m['unite'],
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
                            'prix_moyen' => 0,
                            "unites" => 'ND',
                            "emballage" => 'ND',
                            "quantite_perdu" => 0,
                            "quantite_vendu" => 0,
                            "quantite_autre" => 0,
                            "unite" => $pro->getUnite()
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
                                "unite" => $pro->getUnite()
                            );
                            $resultats[] = $coll;
                        }
                    }
                }
            }

            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
                        "unite" => $m['unite'],
                    );
                    $resultats[] = $coll;
                }
                // $resultats[] = $coll;
            }

            // dd($resultats);
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
                    'emballage' => $m['emballage'],
                    'unite' => $m['unite'],
                );
                $resultats[] = $coll;
            }


            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 400);
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
            return new JsonResponse(['err' => $e->getMessage()], 400);
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

    #[Route(
        'api/collectesEvolutionQuantite',
        name: 'app_collecte_evolution_quantite',
        methods: ['GET', 'POST']
    )]
    public function evlocutionCollecteQuantite(
        Request $request,
        CollecteRepository $collecteRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);


        if ($request->getMethod() == "POST") {
            $dateDebut = $data['dateDebut'];
            $dateFin = $data['dateFin'];
            $collectes = $collecteRepository->getCollecteEvolution($dateDebut, $dateFin);
        } else {
            $collectes = $collecteRepository->getCollecteEvolution(null, null);
        }





        $resultats = [];
        foreach ($collectes as $collecte) {
            $profil = $collecte['profil'];

            $dataItem = [
                'name' => $collecte['date']->format('Y-m-d'),
                'value' => (int) $collecte['quantite']
            ];

            if (isset($resultats[$profil])) {
                $resultats[$profil]['series'][] = $dataItem;
            } else {
                $resultats[$profil] = [
                    'name' => $profil,
                    'series' => [$dataItem]
                ];
            }
        }

        // Add missing dates with a value of 0
        $allDates = []; // Array to store all unique dates
        foreach ($resultats as &$profilData) {
            $series = &$profilData['series'];
            foreach ($series as $dataItem) {
                $allDates[] = $dataItem['name'];
            }
        }

        $allDates = array_unique($allDates); // Remove duplicate dates
        $allDates = array_values($allDates); // Reset array keys

        foreach ($resultats as &$profilData) {
            $series = &$profilData['series'];
            $existingDates = array_column($series, 'name');
            $missingDates = array_diff($allDates, $existingDates);

            foreach ($missingDates as $date) {
                $series[] = [
                    'name' => $date,
                    'value' => 0
                ];
            }

            usort($series, function ($a, $b) {
                return strtotime($a['name']) - strtotime($b['name']);
            });
        }


        return new JsonResponse($resultats, 200, ['Content-Type' => 'application/json']);
    }

    #[Route(
        'api/collectesEvolutionPrix',
        name: 'app_collecte_evolution_prix',
        methods: ['GET', 'POST']
    )]
    public function evlocutionCollectePrix(
        Request $request,
        CollecteRepository $collecteRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);


        if ($request->getMethod() == "POST") {
            $dateDebut = $data['dateDebut'];
            $dateFin = $data['dateFin'];
            $collectes = $collecteRepository->getCollecteEvolutionPrix($dateDebut, $dateFin);
        } else {
            $collectes = $collecteRepository->getCollecteEvolutionPrix(null, null);
        }


        $resultats = [];
        foreach ($collectes as $collecte) {
            $profil = $collecte['profil'];

            $dataItem = [
                'name' => $collecte['date']->format('Y-m-d'),
                'value' =>  (int) $collecte['prix']
            ];

            if (isset($resultats[$profil])) {
                $resultats[$profil]['series'][] = $dataItem;
            } else {
                $resultats[$profil] = [
                    'name' => $profil,
                    'series' => [$dataItem]
                ];
            }
        }

        // Add missing dates with a value of 0
        $allDates = []; // Array to store all unique dates
        foreach ($resultats as &$profilData) {
            $series = &$profilData['series'];
            foreach ($series as $dataItem) {
                $allDates[] = $dataItem['name'];
            }
        }

        $allDates = array_unique($allDates); // Remove duplicate dates
        $allDates = array_values($allDates); // Reset array keys

        foreach ($resultats as &$profilData) {
            $series = &$profilData['series'];
            $existingDates = array_column($series, 'name');
            $missingDates = array_diff($allDates, $existingDates);

            foreach ($missingDates as $date) {
                $series[] = [
                    'name' => $date,
                    'value' => 0
                ];
            }

            usort($series, function ($a, $b) {
                return strtotime($a['name']) - strtotime($b['name']);
            });
        }


        return new JsonResponse($resultats, 200, ['Content-Type' => 'application/json']);
    }

    #[Route(
        'api/collectesEvolutionSaison',
        name: 'app_collecte_evolution_saison',
        methods: ['GET', 'POST']
    )]
    public function evolutionCollecteSaison(
        Request $request,
        CollecteRepository $collecteRepository,
        SaisonRepository $saisonRepository
    ): JsonResponse {

        $saisons = $saisonRepository->findAll();


        $data = json_decode($request->getContent(), true);

        // if ($request->getMethod() == "POST") {
        //     $dateDebut = $data['dateDebut'];
        //     $dateFin = $data['dateFin'];
        //     $collectes = $collecteRepository->getCollecteEvolutionQuantite($dateDebut, $dateFin);
        // } else {
        //     $collectes = $collecteRepository->getCollecteEvolutionQuantite(null, null);
        // }

        $resultats_all = [];
        foreach ($saisons as  $value) {

            $collectes = $collecteRepository->getCollecteEvolutionSaison(
                $value->getDebut()->format('Y-m-d'),
                $value->getFin()->format('Y-m-d')
            );

            $resultats = [];
            $resultats = [];
            foreach ($collectes as $collecte) {
                $profil = $collecte['profil'];
                $dataItem = [
                    'name' => $collecte['date']->format('Y-m-d'),
                    'value' => (int) $collecte['prix'],
                    'quantite' => (int) $collecte['quantite']
                ];

                if (isset($resultats[$profil])) {
                    $resultats[$profil]['series'][] = $dataItem;
                } else {
                    $resultats[$profil] = [
                        'name' => $profil,
                        'series' => [$dataItem]
                    ];
                }
            }
            $allDates = [];
            foreach ($resultats as &$profilData) {
                $series = &$profilData['series'];
                foreach ($series as $dataItem) {
                    $allDates[] = $dataItem['name'];
                }
            }

            $allDates = array_unique($allDates);
            $allDates = array_values($allDates);

            foreach ($resultats as &$profilData) {
                $series = &$profilData['series'];
                $existingDates = array_column($series, 'name');
                $missingDates = array_diff($allDates, $existingDates);

                foreach ($missingDates as $date) {
                    $series[] = [
                        'name' => $date,
                        'value' => 0,
                        'quantite' => 0
                    ];
                }

                usort($series, function ($a, $b) {
                    return strtotime($a['name']) - strtotime($b['name']);
                });
            }
            $resultats_all[$value->getNom() . '-' . $value->getAnnee()] = $resultats;
        }

        return new JsonResponse($resultats_all, 200, ['Content-Type' => 'application/json']);
    }
}
