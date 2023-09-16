<?php

namespace App\Controller;

use App\Entity\Unites;
use App\Entity\User;
use App\Repository\CollecteRepository;
use App\Repository\ConditionnementsProduitsUnitesRepository;
use App\Repository\DepartementRepository;
use App\Repository\ProduitsRepository;
use App\Repository\ProfilsRepository;
use App\Repository\RegionRepository;
use App\Repository\UnitesRepository;
use App\Repository\UserMobileRepository;
use App\Repository\ZonesRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

class MyUnitesController extends AbstractController
{

    public $eventDispatcher;

    private $serializer;
    public function __construct(EventDispatcherInterface $eventDispatcher, SerializerInterface $serializer)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
    }


    public function fn_mesConditionnement_per_laiterie(
        ConditionnementsProduitsUnitesRepository $repo,
        UnitesRepository $laiterieRepository,
        ProduitsRepository $produitsRepository,
        string $idLaiterie,
        string $idProduit
    ) {

        $owner = $this->fn_me();
        if (null == $owner) {
            return [];
        }
        $laiterie = $laiterieRepository->findOneBy(['id' => $idLaiterie]);
        if ($laiterie ==  null) {
            throw  new  NotFoundHttpException(" Unité $idLaiterie  is not found");
        }
        $produit =  $produitsRepository->findOneBy(['id' => $idProduit]);
        if ($produit ==  null) {
            throw  new  NotFoundHttpException(" Produit $idProduit  is not found");
        }


        return  $repo->findBy(['owner' => $owner, 'laiterie' => $laiterie, 'produit' => $produit]);
    }
    private function fn_me(): ?User
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The Security Bundle is not registered in your application.');
        }
        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return  null;
        }
        if (!is_object($user = $token->getUser())) {
            return null;
        }
        return $user;
    }


    /**
     * @Route("/api/unitesSimple/{itemsPerPage}/{page}", name="app_unites_simple",methods={"GET"})
     */
    public function unitesSimple(
        int $itemsPerPage,
        int $page,
        EntityManagerInterface $em,
    ): Response {
        $resultats = [];
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('u')
            ->addSelect('COUNT(c.id) as total_collectes')
            ->addSelect('SUM(CASE WHEN c.isCertified = 1 THEN 1 ELSE 0 END) as nombre_collectes_certifiees')
            ->addSelect('SUM(CASE WHEN c.isCertified = 0 AND c.toCorrect = 0 AND c.isDeleted = 0 THEN 1 ELSE 0 END) as nombre_collectes_non_certifiees')
            ->addSelect('SUM(CASE WHEN c.toCorrect = 1 THEN 1 ELSE 0 END) as nombre_collectes_a_corriger')
            ->addSelect('SUM(CASE WHEN c.isDeleted = 1 THEN 1 ELSE 0 END) as nombre_collectes_supprimees')
            ->from(Unites::class, 'u')
            ->leftJoin('App\Entity\Collecte', 'c', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.unites = u.id')
            ->groupBy('u.id')
            ->setFirstResult(($page) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->orderBy('u.id', 'DESC');
        $results = $queryBuilder->getQuery()->getResult();

        $resultats =  [];
        foreach ($results as $key => $unite) {
            $uniteData = $unite[0]->asArraySimpleWeb();
            $uniteData['nombre_collectes_certifiees'] =  $unite['nombre_collectes_certifiees'];
            $uniteData['nombre_collectes_non_certifiees'] = $unite['nombre_collectes_non_certifiees'];
            $uniteData['nombre_collectes_a_corriger'] =  $unite['nombre_collectes_a_corriger'];
            $uniteData['total_collectes'] = $unite['total_collectes'];
            $uniteData['nombre_collectes_supprimees'] =  $unite['nombre_collectes_supprimees'];
            $uniteData['total_collectes'] =  $unite['total_collectes'];
            //
            $pourcentageCertifiees = ($unite['total_collectes'] !== 0) ? ($unite['nombre_collectes_certifiees'] / $unite['total_collectes']) * 100 : 0;
            $pourcentageNonCertifiees = ($unite['total_collectes'] !== 0) ? ($unite['nombre_collectes_non_certifiees'] / $unite['total_collectes']) * 100 : 0;
            $pourcentageACorriger = ($unite['total_collectes'] !== 0) ? ($unite['nombre_collectes_a_corriger'] / $unite['total_collectes']) * 100 : 0;
            $pourcentageSupprimees = ($unite['total_collectes'] !== 0) ? ($unite['nombre_collectes_supprimees'] / $unite['total_collectes']) * 100 : 0;
            //
            $uniteData['pourcentageCertifiees'] = $pourcentageCertifiees;
            $uniteData['pourcentageNonCertifiees'] =   $pourcentageNonCertifiees;
            $uniteData['pourcentageACorriger'] =  $pourcentageACorriger;
            $uniteData['pourcentageSupprimees'] =  $pourcentageSupprimees;
            $resultats[$key] =  $uniteData;
        }

        return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
    }


    /**
     * @Route("/api/unitesSimple", name="app_unites_simples",methods={"GET"})
     */
    public function unitesSimples(
        EntityManagerInterface $em,
    ): Response {
        $resultats = [];
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('u')
            ->addSelect('COUNT(c.id) as total_collectes')
            ->addSelect('SUM(CASE WHEN c.isCertified = 1 THEN 1 ELSE 0 END) as nombre_collectes_certifiees')
            ->addSelect('SUM(CASE WHEN c.isCertified = 0 AND c.toCorrect = 0 AND c.isDeleted = 0 THEN 1 ELSE 0 END) as nombre_collectes_non_certifiees')
            ->addSelect('SUM(CASE WHEN c.toCorrect = 1 THEN 1 ELSE 0 END) as nombre_collectes_a_corriger')
            ->addSelect('SUM(CASE WHEN c.isDeleted = 1 THEN 1 ELSE 0 END) as nombre_collectes_supprimees')
            ->from(Unites::class, 'u')
            ->leftJoin('App\Entity\Collecte', 'c', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.unites = u.id')
            ->groupBy('u.id')
            ->orderBy('u.id', 'DESC');
        $results = $queryBuilder->getQuery()->getResult();

        $resultats =  [];
        foreach ($results as $key => $unite) {
            $uniteData = $unite[0]->asArraySimpleWeb();
            $uniteData['nombre_collectes_certifiees'] =  $unite['nombre_collectes_certifiees'];
            $uniteData['nombre_collectes_non_certifiees'] = $unite['nombre_collectes_non_certifiees'];
            $uniteData['nombre_collectes_a_corriger'] =  $unite['nombre_collectes_a_corriger'];
            $uniteData['total_collectes'] = $unite['total_collectes'];
            $uniteData['nombre_collectes_supprimees'] =  $unite['nombre_collectes_supprimees'];
            $uniteData['total_collectes'] =  $unite['total_collectes'];
            //
            $pourcentageCertifiees = ($unite['total_collectes'] !== 0) ? ($unite['nombre_collectes_certifiees'] / $unite['total_collectes']) * 100 : 0;
            $pourcentageNonCertifiees = ($unite['total_collectes'] !== 0) ? ($unite['nombre_collectes_non_certifiees'] / $unite['total_collectes']) * 100 : 0;
            $pourcentageACorriger = ($unite['total_collectes'] !== 0) ? ($unite['nombre_collectes_a_corriger'] / $unite['total_collectes']) * 100 : 0;
            $pourcentageSupprimees = ($unite['total_collectes'] !== 0) ? ($unite['nombre_collectes_supprimees'] / $unite['total_collectes']) * 100 : 0;
            //
            $uniteData['pourcentageCertifiees'] = $pourcentageCertifiees;
            $uniteData['pourcentageNonCertifiees'] =   $pourcentageNonCertifiees;
            $uniteData['pourcentageACorriger'] =  $pourcentageACorriger;
            $uniteData['pourcentageSupprimees'] =  $pourcentageSupprimees;
            $resultats[$key] =  $uniteData;
        }

        return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
    }




    // liste des unités d'un depratement donnée
    /**
     * @Route("/api/unites/departement", name="app_unites_departement",methods={"POST"})
     * @param Request $request
     */
    public function find_Unites_Departement(
        Request $request,
        DepartementRepository $departementRepository,
        UnitesRepository $repo
    ): ?Response {
        try {
            $data = json_decode($request->getContent(), true);

            $depart = $data['departement'];
            $departement  = $departementRepository->findOneBy(['nom' => $depart]);


            $laiteries = $repo->findByDeparetement($depart);
            $unites = $repo->findBy(['departement' => $departement]);

            $resultats = array();
            foreach ($laiteries as $m) {
                $resultats[] = $m->asArraygetDepartement();
            }
            if (!$laiteries) {
                throw new EntityNotFoundException("aucune unitée trouvé");
            }

            $jsonData = $this->serializer->serialize($unites, 'json');
            return new JsonResponse(json_decode($jsonData, true), 200, array('Access-Control-Allow-Origin' => '*'));
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 400);
        }
    }


    /**
     * @Route("api/unites/create",name="app_unites_create",methods={"POST"})
     */
    public function create_Unites(
        ProfilsRepository $pr,
        Request $request,
        UserMobileRepository $umr,
        DepartementRepository $dr,
        RegionRepository $rr,
        ZonesRepository $zr,
        UnitesRepository $unitesRepository,
        EntityManagerInterface $em
    ): Response {

        try {
            $data = json_decode($request->getContent(), true);
            $file = 'config/coordinates.json';
            $jsonData = file_get_contents($file);
            $data_localisation = json_decode($jsonData, true);

            $nom =  $data['nom'];
            $telephone = $data['telephone'];

            // $unites_existe_telephone = $unitesRepository->findBy(['telephone' => $telephone]);
            // if (count($unites_existe_telephone) != 0) {
            //     return new JsonResponse("Ce numero téléphone est déja utilisé. Merci de réessayer avec un autre numéro", 400);
            // }

            $email = $data['email'];
            // if ($email != "" || $email != null) {
            //     $unites_existe_email = $unitesRepository->findBy(['email' => $email]);

            //     if (count($unites_existe_email) != 0) {
            //         return new JsonResponse("Ce adresse mail est déja utilisé. Merci de réessayer avec un autre adresse email", 400);
            //     }
            // }

            $adresse = $data['adresse'];
            $isSynchrone = $data['isSynchrone'];
            $isCertified = $data['isCertified'];
            $idprofil = $data['idProfil'];
            $localite = $data['localite'];
            $profil = $pr->find($idprofil);
            $iddepartement = $data['idDepartement'];
            $departement = $dr->find($iddepartement);

            $latitude = $data['latitude'] != 0 || $data['latitude'] != '0' ? $data['latitude'] :  $data_localisation[$departement->getNom()]['latitude'];
            $longitude = $data['longitude'] != 0 || $data['longitude'] != '0'  ? $data['longitude'] : $data_localisation[$departement->getNom()]['longitude'];

            $idzone = $data['idZone'];
            $zone = $idzone != null  ? ($idzone != 0 ? $zr->find($idzone) : null) :  null;
            // $zone = $zr->find(0);
            $idregion = $data['idRegion'];
            $region  = $rr->find($idregion);
            $prenomProprietaire = $data['prenomProprietaire'];
            $nomProprietaire = $data['nomProprietaire'];
            $idUser = $data['idUser'];
            $user = $umr->find($idUser);
            $email = $email != '' ? $data['email'] : '';
            $createdAt = $data['createdAt'];
            $date = new \DateTime($createdAt);
            $datei = DateTimeImmutable::createFromMutable($date);

            //cas ou l'attribut uuid existe dans les données
            if (array_key_exists('uuid', $data)) {

                $uuid = $data['uuid'];
                // le uuid est different de null et de ''
                if ($uuid != null && $uuid != '' && $uuid != 0) {
                    //on recupere l'unité qui a cette uuid
                    $laiterie = $unitesRepository->findOneBy(['uuid' => $uuid]);
                } else {
                    //on initialise l'unité a null si aucune unité n'a ce uuid
                    $laiterie = null;
                }

                if ($laiterie != null) {
                    // dump($laiterie);
                    // dd($laiterie);
                    // une unité avec cette uuid existe
                    $laiterie->setAdresse($adresse);
                    $laiterie->setCreatedAt($datei);
                    $laiterie->setLocalite($localite);
                    $laiterie->setNom($nom);
                    $laiterie->setTelephone($telephone);
                    $laiterie->setEmail($email);
                    $laiterie->setLatitude($latitude);
                    $laiterie->setLongitude($longitude);
                    $laiterie->setDepartement($departement);
                    $laiterie->setIsCertified($isCertified);
                    $laiterie->setIsSynchrone($isSynchrone);
                    $laiterie->setZone($zone);
                    $laiterie->setRegion($region);
                    $laiterie->setUserMobile($user);
                    $laiterie->setRegion($region);
                    $laiterie->setPrenomProprietaire($prenomProprietaire);
                    $laiterie->setNomProprietaire($nomProprietaire);
                    $laiterie->setProfil($profil);
                } else {
                    //cas ou aucune unité avec cette uuid n'existe pas

                    $laiterie = $unitesRepository->findByCriteria($data['idRegion'], $data['idDepartement'], $data['idZone'] != null ? $data['idZone'] : 0, $data['idProfil'], $data['idUser'], $data['telephone']);
                    // dd($laiterie);
                    // si une unité avec ces meme criteres existe
                    if ($laiterie != null) {

                        $laiterie->setAdresse($adresse);
                        $laiterie->setCreatedAt($datei);
                        $laiterie->setLocalite($localite);
                        $laiterie->setNom($nom);
                        $laiterie->setTelephone($telephone);
                        $laiterie->setEmail($email);
                        $laiterie->setLatitude($latitude);
                        $laiterie->setLongitude($longitude);
                        $laiterie->setDepartement($departement);
                        $laiterie->setIsCertified($isCertified);
                        $laiterie->setIsSynchrone($isSynchrone);
                        $laiterie->setZone($zone);
                        $laiterie->setRegion($region);
                        $laiterie->setUserMobile($user);
                        $laiterie->setRegion($region);
                        $laiterie->setPrenomProprietaire($prenomProprietaire);
                        $laiterie->setNomProprietaire($nomProprietaire);
                        $laiterie->setProfil($profil);
                        $laiterie->setUuid($uuid);
                    }
                    // si une unité avec ces meme criteres n'existe pas
                    else {
                        $laiterie = new Unites();

                        $laiterie->setAdresse($adresse);
                        $laiterie->setCreatedAt($datei);
                        $laiterie->setLocalite($localite);
                        $laiterie->setNom($nom);
                        $laiterie->setTelephone($telephone);
                        $laiterie->setEmail($email);
                        $laiterie->setLatitude($latitude);
                        $laiterie->setLongitude($longitude);
                        $laiterie->setDepartement($departement);
                        $laiterie->setIsCertified($isCertified);
                        $laiterie->setIsSynchrone($isSynchrone);
                        $laiterie->setZone($zone);
                        $laiterie->setRegion($region);
                        $laiterie->setUserMobile($user);
                        $laiterie->setRegion($region);
                        $laiterie->setPrenomProprietaire($prenomProprietaire);
                        $laiterie->setNomProprietaire($nomProprietaire);
                        $laiterie->setProfil($profil);
                        $laiterie->setUuid($uuid);
                    }
                }
            }
            //cas ou l'attribut uuid n'est pas dans les données

            else {


                // on cherche l'unité qui a les memes : zone , departement, region , profil , user , telephone
                $laiterie = $unitesRepository->findByCriteria($data['idRegion'], $data['idDepartement'], $data['idZone'] != null ? $data['idZone'] : 0, $data['idProfil'], $data['idUser'], $data['telephone']);
                // dd($laiterie);
                // si une unité avec ces meme criteres existe

                if ($laiterie) {

                    $laiterie->setAdresse($adresse);
                    $laiterie->setCreatedAt($datei);
                    $laiterie->setLocalite($localite);
                    $laiterie->setNom($nom);
                    $laiterie->setTelephone($telephone);
                    $laiterie->setEmail($email);
                    $laiterie->setLatitude($latitude);
                    $laiterie->setLongitude($longitude);
                    $laiterie->setDepartement($departement);
                    $laiterie->setIsCertified($isCertified);
                    $laiterie->setIsSynchrone($isSynchrone);
                    $laiterie->setZone($zone);
                    $laiterie->setRegion($region);
                    $laiterie->setUserMobile($user);
                    $laiterie->setRegion($region);
                    $laiterie->setPrenomProprietaire($prenomProprietaire);
                    $laiterie->setNomProprietaire($nomProprietaire);
                    $laiterie->setProfil($profil);
                    $laiterie->setUuid(Uuid::v4()->toRfc4122());
                }
                // si une unité avec ces meme criteres n'existe pas
                else {

                    $laiterie = new Unites();
                    $laiterie->setAdresse($adresse);
                    $laiterie->setCreatedAt($datei);
                    $laiterie->setLocalite($localite);
                    $laiterie->setNom($nom);
                    $laiterie->setTelephone($telephone);
                    $laiterie->setEmail($email);
                    $laiterie->setLatitude($latitude);
                    $laiterie->setLongitude($longitude);
                    $laiterie->setDepartement($departement);
                    $laiterie->setIsCertified($isCertified);
                    $laiterie->setIsSynchrone($isSynchrone);
                    $laiterie->setZone($zone);
                    $laiterie->setRegion($region);
                    $laiterie->setUserMobile($user);
                    $laiterie->setRegion($region);
                    $laiterie->setPrenomProprietaire($prenomProprietaire);
                    $laiterie->setNomProprietaire($nomProprietaire);
                    $laiterie->setProfil($profil);
                    $laiterie->setUuid(Uuid::v4()->toRfc4122());
                }
            }

            $em->persist($laiterie);
            $em->flush();

            return new JsonResponse($laiterie->asArray(), 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400, ["Content-Type" => "application/json"]);
        }
        return new Response();
    }


    //trouver les unité qui se ressemble
    /**
     * @Route("api/unites/sames",name="app_unites_same",methods={"POST"})
     */
    public function findSameUnite(
        ProfilsRepository $pr,
        Request $request,
        DepartementRepository $dr,
        RegionRepository $rr,
        ZonesRepository $zr,
        UnitesRepository $unitesRepository,
    ) {
        $data = json_decode($request->getContent(), true);
        $idprofil = $data['idProfil'];
        $profil = $pr->find($idprofil);
        $iddepartement = $data['idDepartement'];
        $departement = $dr->find($iddepartement);
        $idzone = $data['idZone'];
        $zone = $idzone ? $zr->find($idzone) : null;
        $idregion = $data['idRegion'];
        $region  = $rr->find($idregion);

        $critere = [
            // 'email' => $email,
            'localite' => $data['localite'],
            'zone' => $zone,
            'departement' => $departement,
            'region' => $region,
            'telephone' => $data['telephone'],
            'profil' => $profil,
            'prenomProprietaire' => $data['prenomProprietaire'],
            'nomProprietaire' =>   $data['nomProprietaire'],
            'isDeleted' => false
        ];

        $unites = $unitesRepository->findBy($critere);
        $resultats = [];

        foreach ($unites as $key => $value) {
            $resultats[] = $value->asArray();
        }
        return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
    }

    /**
     * @Route("api/unites/associateSames",name="app_unites_associate_same",methods={"POST"})
     */
    public function AssociateSameUnite(
        Request $request,
        UnitesRepository $unitesRepository,
        CollecteRepository $collecteRepository,
        EntityManagerInterface $em,
        ProfilsRepository $pr,
        DepartementRepository $dr,
        RegionRepository $rr,
        ZonesRepository $zr,
    ) {
        $data = json_decode($request->getContent(), true);
        $idUnitePrincipal = $data['idUnitePrincipal'];

        $idprofil = $data['idProfil'];
        $profil = $pr->find($idprofil);
        $iddepartement = $data['idDepartement'];
        $departement = $dr->find($iddepartement);
        $idzone = $data['idZone'];
        $zone = $zr->find($idzone);
        $idregion = $data['idRegion'];
        $region  = $rr->find($idregion);

        $critere = [
            // 'email' => $email,
            'localite' => $data['localite'],
            'zone' => $zone,
            'departement' => $departement,
            'region' => $region,
            'telephone' => $data['telephone'],
            'profil' => $profil,
            'prenomProprietaire' => $data['prenomProprietaire'],
            'nomProprietaire' =>   $data['nomProprietaire'],
            'isDeleted' => false
        ];


        $nuitePrimaire = $unitesRepository->find($idUnitePrincipal);
        $nuitePrimaire->setRang("PRIMAIRE");
        $em->persist($nuitePrimaire);


        $unites = $unitesRepository->findBy($critere);


        foreach ($unites as $key => $value) {
            if ($value !=  $nuitePrimaire) {
                $value->setIsDeleted(true);
                $value->setRang("SECONDAIRE");
                $em->persist($value);


                $collectes = $collecteRepository->findBy(['unites' => $value]);
                $resultats = [];

                foreach ($collectes as  $valueC) {
                    $valueC->setUnites($nuitePrimaire);
                    $em->persist($valueC);
                    $resultats[] = $valueC->asArray();
                }
            }
        }


        $em->flush();

        return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
    }




    //liste des unités d'un utilisateur
    /**
     * @Route("api/unites/userMobile",name="app_unites_user_mobile",methods={"POST"})
     */
    public function updateUserMobile(
        Request $request,
        UserMobileRepository $userMobileRepository,
        UnitesRepository $unitesRepository,
        EntityManagerInterface $em
    ): Response {

        try {
            $data = json_decode($request->getContent(), true);
            $userMobile =  $data['userMobile'];
            $unite = $data['unite'];
            $unites = $unitesRepository->find(['id' => $unite]);
            $user = $userMobileRepository->find(['id' => $userMobile]);
            $unites->setUserMobile(null);
            $unites->setUserMobile($user);
            $em->persist($unites);
            $em->flush();

            return new JsonResponse([$unites->asArray()], 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {

            return new JsonResponse([$e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
        return new Response();
    }

    /**
     * @Route("api/unitesListe",name="app_unites_liste_simple",methods={"GET"})
     */
    public function listeUnites(
        UnitesRepository $unitesRepository,
    ): Response {

        $unites = $unitesRepository->findAll();
        $resultat = [];
        foreach ($unites as $un) {
            $resultat[] = $un->asArray();
        }
        return new JsonResponse($resultat, 200, ["Content-Type" => "application/json"]);
    }




    /**
     * @Route("api/uniteSimpleDemande",name="app_unites_liste_simple_demandes",methods={"GET"})
     */
    public function unitesSimpleDemande(
        UnitesRepository $unitesRepository,
        ZonesRepository $zr,
    ): Response {


        $unites = $unitesRepository->findAll();
        $resultats =  [];
        foreach ($unites as $key => $unite) {
            $resultats[] = $unite->asArraySimpleWeb();
        }
        return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
    }


    //quantite total de l'unite
    /**
     * @Route("api/unitesQuantiteTotal/{id}",name="app_unites_quantite_total",methods={"GET"})
     */
    public function quantiteTotalCollecte(int $id, UnitesRepository $unitesRepository, ProfilsRepository $profilsRepository, CollecteRepository $collecteRepository): Response
    {
        $unite = $unitesRepository->find($id);
        $profil = $profilsRepository->findOneBy(["id" => $unite->getProfil()->getId()]);

        if (!$unite) {
            return new JsonResponse("Unité n'existe pas", 200);
        }
        $periode = $profil->getPeriode();
        $periodes =  $collecteRepository->findDatePeriode();

        $real_periode = $periodes[$periode];

        $start = $real_periode['start'];
        $end = $real_periode['end'];
        $resultats = $collecteRepository->findQuantiteByPeriode($unite->getId(), $start, $end);

        $quantite = $resultats[0]['quantite'] ? $resultats[0]['quantite'] : 0;
        $quantite_vendue = $resultats[0]['quantite_vendue'] ? $resultats[0]['quantite_vendue'] : 0;
        $quantite_perdue = $resultats[0]['quantite_perdue'] ? $resultats[0]['quantite_perdue'] : 0;
        $quantite_autre = $resultats[0]['quantite_autre'] ? $resultats[0]['quantite_autre'] : 0;


        return new JsonResponse(["quantite" => $quantite, "quantite_vendue" => $quantite_vendue, "quantite_perdue" => $quantite_perdue, "quantite_autre" => $quantite_autre], 200);
    }

    #[Route('api/unites/collectes/{id}', name: 'app_unites_collectes', methods: ['GET'])]
    public function setToken(int $id, CollecteRepository $collecteRepository, UnitesRepository $unitesRepository): JsonResponse
    {
        $unite = $unitesRepository->find($id);
        $collectes = $collecteRepository->findBy(["unites" => $unite]);
        return new JsonResponse(count($collectes), 200);
    }


    /**
     * @Route("api/unitesUpdates",name="app_unites_update_all_unites",methods={"GET"})
     */
    public function updateAll(
        UnitesRepository $unitesRepository,
        EntityManagerInterface $em,
    ): JsonResponse {

        $unites = $unitesRepository->findAll();

        foreach ($unites as $key => $value) {
            $value->setIsDeleted(false);
            $em->persist($value);
        }
        $em->flush();
        return new JsonResponse("succee", 200);
    }

    /**
     * @Route("/api/unitesStats", name="app_unites_stats" , methods={"GET"})
     */
    public function statUnite(
        UnitesRepository $unitesRepository,
        EntityManagerInterface $em,
    ): Response {

        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('u')
            ->addSelect('COUNT(c.id) as total_collectes')
            ->addSelect('SUM(CASE WHEN c.isCertified = 1 THEN 1 ELSE 0 END) as nombre_collectes_certifiees')
            ->addSelect('SUM(CASE WHEN c.isCertified = 0 AND c.toCorrect = 0 AND c.isDeleted = 0 THEN 1 ELSE 0 END) as nombre_collectes_non_certifiees')
            ->addSelect('SUM(CASE WHEN c.toCorrect = 1 THEN 1 ELSE 0 END) as nombre_collectes_a_corriger')
            ->addSelect('SUM(CASE WHEN c.isDeleted = 1 THEN 1 ELSE 0 END) as nombre_collectes_supprimees')
            ->from(Unites::class, 'u')
            ->leftJoin('App\Entity\Collecte', 'c', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.unites = u.id')
            ->groupBy('u.id')
            ->setFirstResult((1) * 2)
            ->setMaxResults(2);
        $results = $queryBuilder->getQuery()->getResult();

        $resultats =  [];
        foreach ($results as $key => $unite) {
            $uniteData = $unite[0]->asArray();
            $uniteData['nombre_collectes_certifiees'] =  $unite['nombre_collectes_certifiees'];
            $uniteData['nombre_collectes_non_certifiees'] = $unite['nombre_collectes_non_certifiees'];
            $uniteData['nombre_collectes_a_corriger'] =  $unite['nombre_collectes_a_corriger'];
            $uniteData['total_collectes'] = $unite['total_collectes'];
            $uniteData['nombre_collectes_supprimees'] =  $unite['nombre_collectes_supprimees'];
            $uniteData['total_collectes'] =  $unite['total_collectes'];
            //
            $pourcentageCertifiees = ($unite['total_collectes'] !== 0) ? ($unite['nombre_collectes_certifiees'] / $unite['total_collectes']) * 100 : 0;
            $pourcentageNonCertifiees = ($unite['total_collectes'] !== 0) ? ($unite['nombre_collectes_non_certifiees'] / $unite['total_collectes']) * 100 : 0;
            $pourcentageACorriger = ($unite['total_collectes'] !== 0) ? ($unite['nombre_collectes_a_corriger'] / $unite['total_collectes']) * 100 : 0;
            $pourcentageSupprimees = ($unite['total_collectes'] !== 0) ? ($unite['nombre_collectes_supprimees'] / $unite['total_collectes']) * 100 : 0;
            //
            $uniteData['pourcentageCertifiees'] = $pourcentageCertifiees;
            $uniteData['pourcentageNonCertifiees'] =   $pourcentageNonCertifiees;
            $uniteData['pourcentageACorriger'] =  $pourcentageACorriger;
            $uniteData['pourcentageSupprimees'] =  $pourcentageSupprimees;

            $resultats[$key] =  $uniteData;
        }
        return new JsonResponse($resultats, 200);
    }
}
