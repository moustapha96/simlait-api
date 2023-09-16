<?php

namespace App\Controller;

use App\Entity\UnitesUser;
use App\Entity\User;
use App\Entity\Unites;
use App\Repository\CollecteRepository;
use App\Repository\DepartementRepository;
use App\Repository\ProduitsRepository;
use App\Repository\ProfilsRepository;
use App\Repository\RegionRepository;
use App\Repository\UnitesRepository;
use App\Repository\UnitesUserRepository;
use App\Repository\UserMobileRepository;
use App\Repository\ZonesRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use PhpParser\Node\Stmt\Else_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class MyUnitesUserController extends AbstractController
{

    private $serializer;
    private $em;
    public function __construct(SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->serializer = $serializer;
    }
    // create unities users
    /**
     * @Route("/api/unites_users/create", name="app_unites_user_create" ,methods={"POST"})
     */
    public function Associated_unites_user(Request $request, UnitesUserRepository $repo, UnitesRepository $uniteRe): ?Response
    {
        $data = json_decode($request->getContent(), true);
        $prenom = $data['prenom'];
        $nom = $data['nom'];
        $adresse = $data['adresse'];
        $region = $data['region'];
        $departement = $data['departement'];
        $zone = $data['zone'];
        $profil = $data['profil'];
        try {
            $unites = $uniteRe->findAssociated($region, $departement, $zone, $prenom, $nom, $profil,  $prenom, $nom, $adresse);
            $resultats = array();
            foreach ($unites as $m) {
                if ($repo->isAssociated($m->getUserMobile()->getId(), $m->getId())  == null) {
                    $resultats[] = $m->asArray();
                }
            }

            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(["aucun resultat"], 200, ["Content-Type" => "application/json"]);
        }
    }



    //liste des laiteries dans la zone , departement, region
    /**
     * @Route("/api/unites_users/search", name="app_unites_user_search" ,methods={"POST"})
     */
    public function search_unites_user(Request $request, UserMobileRepository $userMobileRepository, ProfilsRepository $profilsRepository, UnitesUserRepository $repo, UnitesRepository $uniteRe): ?Response
    {
        $data = json_decode($request->getContent(), true);
        $prenom = $data['prenom'];
        $nom = $data['nom'];
        $adresse = $data['adresse'];
        $region = $data['region'];
        $departement = $data['departement'];
        $zone = $data['zone'];
        $profil = $data['profil'];
        $idUserMobile = $data['idUserMobile'];

        try {
            $unites = $uniteRe->findAssociated($region, $departement, $zone, $profil, $idUserMobile, $prenom, $nom, $adresse);

            $resultats = array();
            foreach ($unites as $value) {
                $user = $userMobileRepository->findOneBy(['id' =>  $value->getUserMobile()->getId()]);
                $profils = $profilsRepository->findOneBy(['id' => $user->getProfil()->getId()]);
                if ($profils->getNom() == 'AGENT') {
                    $resultats[] = $value->asArray();
                }
            }

            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(["erreur " . $e->getMessage()], 200, ["Content-Type" => "application/json"]);
        }
    }

    /**
     * @Route("/api/unites_users/isAssociated", name="app_unites_user_isAssociated" ,methods={"POST"})
     */
    public function isAssociated_unites_user(Request $request, UnitesUserRepository $repo, UserMobileRepository $ru, UnitesRepository $rl): ?Response
    {
        $data = json_decode($request->getContent(), true);
        $iduserMobile = $data['idUserMobile'];
        $idUnites = $data['idUnites'];
        $userMobile = $ru->find($iduserMobile);
        $laiterie = $rl->find($idUnites);

        $r = $repo->isAssociated($iduserMobile, $idUnites);

        if ($r != null) {
            return new JsonResponse([true], 200, ["Content-Type" => "application/json"]);
        } else

            return new JsonResponse([false], 200, ["Content-Type" => "application/json"]);
    }

    //liste des unites d'un utilisateur 

    /**
     * @Route("/api/unitesUser" ,  name="app_get_unites_user_mobiles" ,methods={"POST"} )
     */
    public function getUnitesUser(
        Request $request,
        UnitesRepository $unitesRepository,
        UserMobileRepository $userMobileRepository
    ): Response {
        $data = json_decode($request->getContent(), true);
        $idUser = $data['idUser'];
        $user = $userMobileRepository->find($idUser);
        $unites = $unitesRepository->findBy(['userMobile' => $user]);

        $resultats = [];
        foreach ($unites as $key => $unite) {
            $resultats[] = $unite->asArraySimple();
        }
        $jsonData = $this->serializer->serialize($unites, 'json');
        return new JsonResponse($resultats, 200);
    }


    /**
     * @Route("/api/unitesUserCollectes" ,  name="app_get_unites_collecte" ,methods={"POST"} )
     */
    public function getUnitesUserCollecte(
        Request $request,
        CollecteRepository $collecteRepository,
        UnitesRepository $unitesRepository,
        UserMobileRepository $userMobileRepository
    ): Response {
        $data = json_decode($request->getContent(), true);
        $idUser = $data['idUser'];
        $user = $userMobileRepository->find($idUser);
        $unites = $unitesRepository->findBy(['userMobile' => $user]);
        $collectes = [];
        foreach ($unites as $unite) {
            $critere = ['user' => $user, 'unites' => $unite, 'isCertified' => true];
            $collecte = $collecteRepository->findBy($critere, ['dateCollecte' => 'DESC'], 5);

            foreach ($collecte as $value) {
                $collectes[] = $value->asArray();
            }
        }
        // $jsonData = $this->serializer->serialize($collectes, 'json');
        return new JsonResponse($collectes, 200, array('Access-Control-Allow-Origin' => '*'));
    }

    //liste des unites d'un utilisateur
    /**
     * @Route("/api/unites/user/{id}" ,  name="app_get_unites_user" ,methods={"GET"} )
     */
    public function getUnitesUserUnites(
        int $id,
        UnitesRepository $unitesRepository,
        UserMobileRepository $userMobileRepository
    ): Response {

        $user = $userMobileRepository->find($id);
        $unites = $unitesRepository->findBy(['userMobile' => $user], ['id' => 'DESC']);

        $resultats =  [];
        foreach ($unites as $value) {
            $resultats[] = $value->asArray();
        }

        return new JsonResponse($resultats, 200, array('Access-Control-Allow-Origin' => '*'));
    }


    //liste des laiteries dans la zone , departement, region
    /**
     * @Route("/api/unites_users/create", name="app_unites_user_create" ,methods={"POST"})
     */
    public function create_unites_user2(EntityManagerInterface $entityManager, Request $request, UnitesUserRepository $repoUniteUser, UserMobileRepository $userMobileRepository, UnitesRepository $uniteRe): ?Response
    {
        $data = json_decode($request->getContent(), true);
        $idUserMobile = $data['idUserMobile'];
        $idUnites = $data['idUnites'];

        try {
            $userMobile = $userMobileRepository->find($idUserMobile);
            $unites = $uniteRe->find($idUnites);
            $unite_user = new UnitesUser();
            $unite_user->setUnites($unites);
            $unite_user->setUserMobile($userMobile);

            $entityManager->persist($unite_user);
            $entityManager->flush();
            return new JsonResponse(['relation etablie'], 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(["relation non etablie " . $e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
    }
}
