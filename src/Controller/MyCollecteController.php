<?php

namespace App\Controller;

use App\Entity\Collecte;
use App\Repository\CollecteRepository;
use App\Repository\ConditionnementsRepository;
use App\Repository\DepartementRepository;
use App\Repository\EmballageRepository;
use App\Repository\ProduitsRepository;
use App\Repository\ProfilsRepository;
use App\Repository\UnitesRepository;
use App\Repository\UserMobileRepository;
use App\service\ConfigurationService;
use Doctrine\DBAL\Schema\View;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Serializer\SerializerInterface;

class MyCollecteController extends AbstractController
{

    private $serializer;
    private $em;
    private $config;
    public function __construct(
        SerializerInterface $serializer,
        ConfigurationService $config,
        EntityManagerinterface $em
    ) {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->config = $config;
    }

    public function collectesParProfil($profilId): array
    {

        $collectes = $this->em->getRepository(Collecte::class)
            ->createQueryBuilder('c')
            ->addSelect('unite')->join('c.unites', 'unite')
            ->addSelect('profil')->join('unite.profil', 'profil')
            ->where('profil.id = :profilId')
            ->setParameter('profilId', $profilId)
            ->getQuery()
            ->getResult();

        return $collectes;
    }


    /**
     * @Route("/api/collectes/brute/{page}/{itemsPerPage}", name="app_ollecte_brutec",methods={"GET"})
     */
    public function collecteBrute(int $page, int $itemsPerPage, CollecteRepository $collecteRepository): JsonResponse
    {

        $collectes = $collecteRepository->findAllDataBrute($itemsPerPage, $page);
        $resultats = array();
        foreach ($collectes as $m) {
            $resultats[] = $m->asArray();
        }
        return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
    }


    /**
     * @Route("/api/collectes/searchAll", name="app_search_collecte_all",methods={"POST"})
     * @param Request $request
     * @return View
     */
    public function searchAll(Request $request, CollecteRepository $repo): ?Response
    {

        try {
            $data = json_decode($request->getContent(), true);
            $region = $data['region'];
            $department = $data['departement'];
            $produit = $data['produit'];
            $conditionnement = $data['conditionnement'];
            $unites = $data['unites'];
            $emballage = $data['emballage'];
            $dateDebut = $data['dateDebut'];
            $dateFin = $data['dateFin'];
            $zone = $data['zone'];
            $profil = $data['profil'];
            $user = $data['user'];
            $itemsPerPage = $data['itemsPerPage'];
            $page = $data['page'];
            $collectes = $repo->findAllByCriteria(
                $itemsPerPage,
                $page,
                $user,
                $region,
                $department,
                $zone,
                $produit,
                $conditionnement,
                $unites,
                $emballage,
                $dateDebut,
                $dateFin
            );
            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }

            // $jsonData = $this->serializer->serialize($collectes, 'json');
            // return new JsonResponse(json_decode($jsonData, true), 200);

            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 400);
        }
    }



    /**
     * @Route("/api/collectes/search", name="app_search_collecte",methods={"POST"})
     * @param Request $request
     * @return View
     */
    public function search(Request $request, CollecteRepository $repo): ?Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            $region = $data['produit'];
            $department = $data['departement'];
            $produit = $data['produit'];
            $conditionnement = $data['conditionnement'];
            $unites = $data['unites'];
            $emballage = $data['emballage'];
            $dateDebut = $data['dateDebut'];
            $dateFin = $data['dateFin'];
            $zone = $data['zone'];
            $profil = $data['profil'];
            $collectes = $repo->findParCriteria(
                $profil,
                $region,
                $department,
                $zone,
                $produit,
                $conditionnement,
                $unites,
                $emballage,
                $dateDebut,
                $dateFin,
            );
            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/api/collectes/certifiedAll", name="app_certified_collecte",methods={"GET"})
     * @param Request $request
     * @return View
     */
    public function enableCertificate(CollecteRepository $repo, EntityManagerInterface $entityManagerInterface): ?Response
    {
        try {

            $collectes = $repo->findAll();
            foreach ($collectes as $m) {
                $m->setIsCertified(true);
                $entityManagerInterface->persist($m);
                $entityManagerInterface->flush();
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


    /**
     * @Route("/api/collectes/findUnitesWithDemande", name="app_collecte_findUnitesWithDemande",methods={"POST"})
     * @param Request $request
     * @return View
     */
    public function findUnitesWithDemande(Request $request, CollecteRepository $repo): ?Response
    {

        try {
            $data = json_decode($request->getContent(), true);
            $zone = $data['zone'];
            $besoin = $data['besoin'];
            $produit = $data['produit'];
            $dateDebut = $data['dateDebut'];
            $dateFin = $data['dateFin'];
            $typeUnite = $data['typeUnite'];
            // dd($data);
            if ($typeUnite == 'unites') {
                $collectes = $repo->findUnitesWithDemandeUnites($zone, $besoin, $produit, $dateDebut, $dateFin);
            } else if ($typeUnite == "unitesAutre") {
                $collectes = $repo->findUnitesWithDemandeUnitesAutre('', $besoin, $produit, $dateDebut, $dateFin);
            }

            return new JsonResponse($collectes, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([], 200, ["Content-Type" => "application/json"]);
        }
    }

    //la tailes de chaque type de collecte 
    /**
     * @Route("/api/sizeOfTypeCollecte", name="app_collecte_SIZE_TYPE",methods={"GET"})
     */
    public function SzieOfTypeCollecte(): Response
    {

        $commercant = $this->collectesParProfil(3);
        $collecteur =  $this->collectesParProfil(4);
        $eleveur =  $this->collectesParProfil(5);
        $producteur =  $this->collectesParProfil(6);
        $transformateur =  $this->collectesParProfil(7);

        return new JsonResponse([
            "collecteurs" => count($collecteur),
            "commercants" => count($commercant),
            "eleveurs" => count($eleveur),
            "producteurs" => count($producteur),
            "transformateurs" => count($transformateur)
        ], 200, ["Content-Type" => "application/json"]);
    }
    /**
     * @Route("/api/collectes/create",name="app_collectes_create",methods={"POST"})
     */
    public function createCollecte(
        Request $request,
        ProduitsRepository $pr,
        ConditionnementsRepository $cr,
        UnitesRepository $lr,
        UserMobileRepository $ur,
        EmballageRepository $er,
        EntityManagerInterface $em,
        CollecteRepository $collecteRepository
    ): Response {

        try {
            $data = json_decode($request->getContent(), true);
            $uuid = $data['uuid'];
            $collecte = $collecteRepository->findOneBy(['uuid' => $uuid]);
            $idProduit =  $data['idProduit'];
            $idConditionnement = $data['idConditionnement'];
            $idUnites = $data['idUnites'];
            $idUser = $data['idUser'];
            $idEmballage = $data['idEmballage'];
            $isSynchrone = $data['isSynchrone'];
            $isCertified = $data['isCertified'];
            $quantite = $data['quantite'];
            $prix = $data['prix'];
            $isDeleted = $data['isDeleted'];
            $quantite_vendu = $data['quantite_vendu'];
            $quantite_perdu = $data['quantite_perdu'];
            $quantite_autre = $data['quantite_autre'];


            $produit = $pr->find($idProduit);
            if (!$produit) {
                return new JsonResponse("le produit choisi n'existe pas ", 400);
            }
            $conditionnement = $cr->find($idConditionnement);
            if (!$conditionnement) {
                return new JsonResponse("le conditionnement choisi n'existe pas ", 400);
            }
            $unite = $lr->find($idUnites);
            if (!$unite) {
                return new JsonResponse("l'unité laitière choisie n'existe pas ", 400);
            }
            $user = $ur->find($idUser);
            if (!$user) {
                return new JsonResponse("l'utilsateur n'exsite  n'existe pas ", 400);
            }
            $emballage = $er->find($idEmballage);
            if (!$emballage) {
                return new JsonResponse("l'emballage n'exsite  n'existe pas ", 400);
            }

            $dateSaisieinitial = $data['dateSaisie'];
            $dateSaisieFormater = new \DateTime($dateSaisieinitial);
            $dateSaisie = \DateTimeImmutable::createFromMutable($dateSaisieFormater);

            $dateCollecte = $data['dateCollecte'];
            $date = new \DateTime($dateCollecte);
            $datei = \DateTimeImmutable::createFromMutable($date);
            $toCorrect = $data['toCorrect'];


            $uniteEnableCertification = $this->config->get('certificationUnite');
            if ($uniteEnableCertification == "enable") {

                if ($unite->isIsCertified() == false) {
                    return new JsonResponse("Merci de demander une certification de cette unité pour continuer à collecter", 400);
                } else {
                    if ($collecte) {

                        $collecte->setConditionnements($conditionnement);
                        $collecte->setEmballages($emballage);
                        $collecte->setUser($user);
                        $collecte->setUnites($unite);
                        $collecte->setProduits($produit);
                        $collecte->setQuantite($quantite);
                        $collecte->setPrix($prix);
                        $collecte->setIsCertified($isCertified);
                        $collecte->setIsDeleted($isDeleted);
                        $collecte->setDateCollecte($datei);
                        $collecte->setIsSynchrone($isSynchrone);
                        $collecte->setDateSaisie($dateSaisie);
                        $collecte->setQuantitePerdu($quantite_perdu);
                        $collecte->setQuantiteAutre($quantite_autre);
                        $collecte->setQuantiteVendu($quantite_vendu);
                        $collecte->setToCorrect($toCorrect);
                        $em->persist($collecte);
                    } else {
                        $collecte = new Collecte();
                        $collecte->setConditionnements($conditionnement);
                        $collecte->setEmballages($emballage);
                        $collecte->setUser($user);
                        $collecte->setUnites($unite);
                        $collecte->setProduits($produit);
                        $collecte->setQuantite($quantite);
                        $collecte->setPrix($prix);
                        $collecte->setIsCertified($isCertified);
                        $collecte->setIsDeleted($isDeleted);
                        $collecte->setToCorrect($toCorrect);
                        $collecte->setDateCollecte($datei);
                        $collecte->setIsSynchrone($isSynchrone);
                        $collecte->setDateSaisie($dateSaisie);
                        $collecte->setQuantitePerdu($quantite_perdu);
                        $collecte->setQuantiteAutre($quantite_autre);
                        $collecte->setQuantiteVendu($quantite_vendu);
                        $collecte->setToCorrect($toCorrect);

                        $collecte->setUuid($uuid);

                        $em->persist($collecte);
                    }
                }
            } else {

                if ($collecte) {

                    $collecte->setConditionnements($conditionnement);
                    $collecte->setEmballages($emballage);
                    $collecte->setUser($user);
                    $collecte->setUnites($unite);
                    $collecte->setProduits($produit);
                    $collecte->setQuantite($quantite);
                    $collecte->setPrix($prix);
                    $collecte->setIsCertified($isCertified);
                    $collecte->setIsDeleted($isDeleted);
                    $collecte->setDateCollecte($datei);
                    $collecte->setIsSynchrone($isSynchrone);
                    $collecte->setDateSaisie($dateSaisie);
                    $collecte->setQuantitePerdu($quantite_perdu);
                    $collecte->setQuantiteAutre($quantite_autre);
                    $collecte->setQuantiteVendu($quantite_vendu);
                    $collecte->setToCorrect($toCorrect);
                    $em->persist($collecte);
                } else {
                    $collecte = new Collecte();
                    $collecte->setConditionnements($conditionnement);
                    $collecte->setEmballages($emballage);
                    $collecte->setUser($user);
                    $collecte->setUnites($unite);
                    $collecte->setProduits($produit);
                    $collecte->setQuantite($quantite);
                    $collecte->setPrix($prix);
                    $collecte->setIsCertified($isCertified);
                    $collecte->setIsDeleted($isDeleted);
                    $collecte->setToCorrect($toCorrect);
                    $collecte->setDateCollecte($datei);
                    $collecte->setIsSynchrone($isSynchrone);
                    $collecte->setDateSaisie($dateSaisie);
                    $collecte->setQuantitePerdu($quantite_perdu);
                    $collecte->setQuantiteAutre($quantite_autre);
                    $collecte->setQuantiteVendu($quantite_vendu);
                    $collecte->setToCorrect($toCorrect);

                    $collecte->setUuid($uuid);

                    $em->persist($collecte);
                }
            }

            $em->flush();
            return new JsonResponse([$collecte->asArray()], 201, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
    }


    /**
     * @Route("/api/eleveurs/{page}/{itemsPerPage}", name="app_collecte_eleveur" , methods={"GET"})
     */
    public function findCollecteEleveur(int $page, int $itemsPerPage, CollecteRepository $collecteRepository): ?Response
    {
        try {
            $collectes  = $collecteRepository->findCollecteEleveur($itemsPerPage, $page);
            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
    }


    /**
     * @Route("/api/producteurs/{page}/{itemsPerPage}", name="app_collecte_producteur" , methods={"GET"})
     */
    public function findCollecteProducteur(int $page, int $itemsPerPage, CollecteRepository $collecteRepository): ?Response
    {
        try {
            $collectes  = $collecteRepository->findCollecteProducteur($itemsPerPage, $page);
            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
    }


    /**
     * @Route("/api/collecteurs/{page}/{itemsPerPage}", name="app_collecte_collecteurs" , methods={"GET"})
     */
    public function findCollecteCollecteur(int $page, int $itemsPerPage, CollecteRepository $collecteRepository): ?Response
    {
        try {
            $collectes  = $collecteRepository->findCollecteCollecteur($itemsPerPage, $page);
            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
    }

    /**
     * @Route("/api/commercants/{page}/{itemsPerPage}", name="app_collecte_commercants" , methods={"GET"})
     */
    public function findCollectecommercants(int $page, int $itemsPerPage, CollecteRepository $collecteRepository): ?Response
    {
        try {
            $collectes  = $collecteRepository->findCollecteCommercants($itemsPerPage, $page);
            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
    }

    /**
     * @Route("/api/transformateurs/{page}/{itemsPerPage}", name="app_collecte_transformateurs" , methods={"GET"})
     */
    public function findCollectetransformateurs(int $page, int $itemsPerPage, CollecteRepository $collecteRepository): ?Response
    {
        try {
            $collectes  = $collecteRepository->findCollecteTransformateur($itemsPerPage, $page);
            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
    }

    // fonction retournant le pourcentage de chaque agent de ces collecte certifier et non certifier 
    /**
     * @Route("/api/collectes/percent/{id}", name="app_collecte_percent" , methods={"GET"})
     */
    public function getPercentCollecteAgent(int $id, CollecteRepository $collecteRepository, UserMobileRepository $userMobileRepository): ?Response
    {
        try {

            $user = $userMobileRepository->find($id);
            $criteter = ['user' => $user, 'isCertified' => true];
            $toccorect_no = ['user' => $user, 'toCorrect' => true];
            if (!$user) {
                return new JsonResponse("user not found", 200, ["Content-Type" => "application/json"]);
            }

            $collectes_certifier  = $collecteRepository->findBy($criteter);
            $collectes_to_correct  = $collecteRepository->findBy($toccorect_no);
            $collectes = $collecteRepository->findBy(['user' => $user]);


            if (!$collectes) {
                return new JsonResponse(['Certified' => 0, "noCertified" => 0, "toCorrect" =>  (string) 0,  "collectes" => count($collectes)], 200, ["Content-Type" => "application/json"]);
            }

            $percent = (count($collectes_certifier) * 100) / count($collectes);
            $no_certifier = 100 - $percent;

            return new JsonResponse([
                'Certified' => number_format($percent, 1, '.', ' '),
                "noCertified" => number_format($no_certifier, 1, '.', ' '),
                "toCorrect" =>  (string) count($collectes_to_correct),
                "collectes" => count($collectes)
            ], 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
    }

    /**
     * @Route("/api/collectesToCorrect/user/{id}", name="app_collecte_user" , methods={"GET"})
     */
    public function getCollecteUser(int $id, CollecteRepository $collecteRepository, UserMobileRepository $userMobileRepository): ?Response
    {
        try {

            $user = $userMobileRepository->find($id);
            $criteter = ['user' => $user, 'toCorrect' => '1'];
            $collectes = $collecteRepository->findBy($criteter);

            $jsonData = $this->serializer->serialize($collectes, 'json');
            return new JsonResponse(json_decode($jsonData, true), 200);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
    }



    /**
     * @Route("/api/collectes/users/{idUser}/unites/{idUnite}", name="app_collecte_unites_user" , methods={"GET"})
     */
    public function getCollecteUniteUser(
        int $idUser,
        int $idUnite,
        CollecteRepository $collecteRepository,
        UserMobileRepository $userMobileRepository,
        UnitesRepository $unitesRepository
    ): ?Response {
        try {

            $user = $userMobileRepository->find($idUser);
            $unite = $unitesRepository->find($idUnite);

            $criteter = ['user' => $user, 'unites' => $unite];
            $collectes = $collecteRepository->findBy($criteter);
            $resultats = [];
            foreach ($collectes as $value) {
                $resultats[] = $value->asArray();
            }

            // $jsonData = $this->serializer->serialize($collectes, 'json'); 
            // return new JsonResponse(json_decode($jsonData, true), 200);
            return new JsonResponse($resultats, 200);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
    }

    /**
     * @Route("/api/collectes/departement/{departement}/{page}/{itemsPerPage}", name="app_collecte_user_zone" , methods={"GET"})
     */
    public function findCollecteUserZone(
        int $page,
        int $itemsPerPage,
        string $departement,
        CollecteRepository $collecteRepository
    ): ?Response {

        try {
            $collectes  = $collecteRepository->getCollecteDepartement($departement, $page, $itemsPerPage);
            $resultats = [];
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
    }
    /**
     * @Route("/api/collectes/zone", name="app_collecte_user_zone_intervention" , methods={"POST"})
     */
    public function findCollecteUserZoneintervention(
        Request $request,
        CollecteRepository $collecteRepository
    ): ?Response {
        $data = json_decode($request->getContent(), true);

        $zone = $data['zone'];
        $page = $data['page'];
        $pageSize = $data['pageSize'];

        try {
            $collectes  = $collecteRepository->getCollecteZoneIntervention($zone, $page, $pageSize);
            $resultats = [];
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
    }

    /**
     *@Route("/api/collectes/zone/size", name= "api_collectes_zone_size", methods={"POST"})
     */
    public function getSizeCollecteZoneIntervention(Request $request, CollecteRepository $collecteRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $collectes  = count($collecteRepository->getCollecteZoneSize($data['zone']));
            return new JsonResponse($collectes, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
    }


    /**
     *@Route("/api/collectes/departement/{departement}/size", name= "api_collectes_departement_size", methods={"GET"})
     */
    public function getSizeCollecte(string $departement, CollecteRepository $collecteRepository): JsonResponse
    {
        try {
            $collectes  = count($collecteRepository->getCollecteDepartementSize($departement));
            return new JsonResponse($collectes, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
    }
}
