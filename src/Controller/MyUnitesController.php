<?php

namespace App\Controller;

use App\Entity\Unites;
use App\Entity\User;
use App\EventListener\Unites\UniteCreateEvent;
use App\EventListener\Unites\UniteEventSubscriber;
use App\Repository\ConditionnementsProduitsUnitesRepository;
use App\Repository\DepartementRepository;
use App\Repository\NotificationRepository;
use App\Repository\ParametrageMobileRepository;
use App\Repository\ProduitsRepository;
use App\Repository\ProfilsRepository;
use App\Repository\RegionRepository;
use App\Repository\UnitesRepository;
use App\Repository\UserMobileRepository;
use App\Repository\ZonesRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Schema\View;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class MyUnitesController extends AbstractController
{

    public $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
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
            throw  new  NotFoundHttpException(" Laiteries $idLaiterie  is not found");
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
     * @Route("/api/unites/departement", name="app_unites_departement",methods={"POST"})
     * @param Request $request
     */
    public function find_Unites_Departement(Request $request, UnitesRepository $repo): ?Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            $department = $data['departement'];
            // $profil = $data['profil'];
            // $idUserMobile = $data['idUserMobile'];

            $laiteries = $repo->findByDeparetement($department);

            $resultats = array();
            foreach ($laiteries as $m) {
                $resultats[] = $m->asArraygetDepartement();
            }
            if (!$laiteries) {
                throw new EntityNotFoundException("aucune unitée trouvé");
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
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
        EntityManagerInterface $em
    ): Response {

        try {
            $data = json_decode($request->getContent(), true);
            $nom =  $data['nom'];
            $telephone = $data['telephone'];
            $email = $data['email'];
            $latitude = $data['latitude'];
            $longitude = $data['longitude'];
            $adresse = $data['adresse'];
            $isSynchrone = $data['isSynchrone'];
            $isCertified = $data['isCertified'];

            $idprofil = $data['idProfil'];
            $localite = $data['localite'];
            $profil = $pr->find($idprofil);
            $iddepartement = $data['idDepartement'];
            $departement = $dr->find($iddepartement);

            $idzone = $data['idZone'];
            $zone = $zr->find($idzone);

            $idregion = $data['idRegion'];
            $region  = $rr->find($idregion);

            $prenomProprietaire = $data['prenomProprietaire'];
            $nomProprietaire = $data['nomProprietaire'];
            $idUser = $data['idUser'];
            $user = $umr->find($idUser);

            $createdAt = $data['createdAt'];
            $date = new \DateTime($createdAt);

            $datei = DateTimeImmutable::createFromMutable($date);

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
            $em->persist($laiterie);
            $em->flush();


            return new JsonResponse([$laiterie->asArray()], 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {

            return new JsonResponse([$e->getMessage()], 500, ["Content-Type" => "application/json"]);
        }
        return new Response();
    }
}
