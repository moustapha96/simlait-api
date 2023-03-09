<?php


namespace App\Controller;

use App\Entity\UserMobile;
use App\Repository\DepartementRepository;
use App\Repository\ProfilRepository;
use App\Repository\ProfilsRepository;
use App\Repository\RegionRepository;
use App\Repository\StatusRepository;
use App\Repository\UnitesRepository;
use App\Repository\UserMobileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyUserMobileController extends AbstractController
{

    /**
     * @Route("/api/user_mobiles/getLaiteries", name="app_getLaiterie_user_mobile",methods={"POST"})
     * @param Request $request
     */
    public function getLaiteriesUser(Request $request, UnitesRepository $repo): ?Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            $email = $data['email'];

            $laiteries = $repo->findLaiterieUser($email);
            $resultats = array();
            foreach ($laiteries as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/api/user_mobiles/create", name="app_user_mobile_create",methods={"POST"})
     * @param Request $request
     */
    public function createUserMobile(Request $request, UserMobileRepository $userMobileRepository, StatusRepository $str, RegionRepository $rr, ProfilsRepository $pr, DepartementRepository $dr, EntityManagerInterface $em): ?Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $email = $data['email'] || null;
            $prenom = $data['prenom'];
            $nom = $data['nom'];
            $adresse = $data['adresse'];
            $telephone = $data['telephone'];
            $userExit = $userMobileRepository->findOneBy(['telephone' => $telephone]);
            if ($userExit) {
                return new Response("Numéro de téléphone existe déjà", 400);
            }

            $sexe = $data['sexe'];
            $roles = $data['roles'];
            $enabled = $data['enabled'];
            $password = $data['password'];
            $idStatus = $data['idStatus'];
            $uuid = $data['uuid'];
            $iddepartement = $data['idDepartement'];
            $departement = $dr->find($iddepartement);
            $idregion = $data['idRegion'];
            $region = $rr->find($idregion);
            $hasLaiteries = $data['hasLaiteries'];
            $idprofil = $data['idProfil'];
            $localite = $data['localite'];
            $profil = $pr->find($idprofil);
            $status = $str->find($idStatus);

            $user = new UserMobile();
            $user->setEmail($email);
            $user->setLocalite($localite);
            $user->setAdresse($adresse);
            $user->setPrenom($prenom);
            $user->setNom($nom);
            $user->setTelephone($telephone);
            $user->setSexe($sexe);
            $user->setEnabled($enabled);
            $user->setUuid($uuid);
            $user->setRegion($region);
            $user->setDepartement($departement);
            $user->setStatus($status);
            $user->setRoles($roles);
            $user->setPassword($password);
            $user->setHasLaiteries($hasLaiteries);
            $user->setProfil($profil);

            $em->persist($user);
            $em->flush();

            return new Response("user bien creer", 201);
        } catch (\Exception $e) {
            return new Response("utilisateur non creer $e ", 400);
        }
    }

    /**
     * @Route("/api/user_mobiles/update", name="app_user_mobile_update",methods={"POST"})
     * @param Request $request
     */
    public function updateUserMobile(Request $request, UserMobileRepository $userMobileRepository, StatusRepository $str, ProfilsRepository $pr, UserMobileRepository $ru, RegionRepository $rr, DepartementRepository $dr, EntityManagerInterface $em): ?Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $id = $data['id'];
            $email = $data['email'] || null;
            $prenom = $data['prenom'];
            $nom = $data['nom'];
            $adresse = $data['adresse'];
            $telephone = $data['telephone'];
            $userExit = $userMobileRepository->findOneBy(['telephone' => $telephone]);
            if ($userExit) {
                return new Response("Numéro de téléphone existe déjà", 400);
            }

            $sexe = $data['sexe'];
            $roles = $data['roles'];
            $enabled = $data['enabled'];
            $password = $data['password'];
            $idStatus = $data['idStatus'];
            $uuid = $data['uuid'];
            $iddepartement = $data['idDepartement'];
            $departement = $dr->find($iddepartement);
            $idregion = $data['idRegion'];
            $region = $rr->find($idregion);
            $hasLaiteries = $data['hasLaiteries'];
            $idprofil = $data['idProfil'];
            $localite = $data['localite'];
            $status = $str->find($idStatus);
            $profil = $pr->find($idprofil);


            $user = $ru->find($id);
            $user->setEmail($email);
            $user->setLocalite($localite);
            $user->setAdresse($adresse);
            $user->setPrenom($prenom);
            $user->setNom($nom);
            $user->setTelephone($telephone);
            $user->setSexe($sexe);
            $user->setEnabled($enabled);
            $user->setUuid($uuid);
            $user->setRegion($region);
            $user->setDepartement($departement);
            $user->setStatus($status);
            $user->setRoles($roles);
            $user->setPassword($password);
            $user->setHasLaiteries($hasLaiteries);
            $user->setProfil($profil);

            $em->persist($user);
            $em->flush();

            return new Response("user bien mise a jour", 200);
        } catch (\Exception $e) {
            return new Response("utilisateur non mise à jour $e ", 400);
        }
    }
}
