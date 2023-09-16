<?php


namespace App\Controller;

use App\Entity\Departement;
use App\Entity\UserMobile;
use App\Entity\Status;
use App\Entity\Region;
use App\Entity\Profils;

use App\Repository\DepartementRepository;
use App\Repository\ModelSmsRepository;
use App\Repository\ProfilsRepository;
use App\Repository\RegionRepository;
use App\Repository\StatusRepository;
use App\Repository\UnitesRepository;
use App\Repository\UserMobileRepository;
use App\service\OrangeSMSService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class MyUserMobileController extends AbstractController
{


    private $entityManager;


    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }


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
            return new JsonResponse(['err' => $e->getMessage()], 400);
        }
    }



    /**
     * @Route("/api/user_mobiles/create", name="app_user_mobile_create",methods={"POST"})
     * @param Request $request
     */
    public function createUserMobile(
        Request $request,
        UserMobileRepository $userMobileRepository,
        OrangeSMSService  $orangeSMSService,
        StatusRepository $str,
        RegionRepository $rr,
        ProfilsRepository $pr,
        DepartementRepository $dr,
        ModelSmsRepository $modelSmsRepository
    ): ?Response {

        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $telephone = $data['telephone'];
        $userExit = $userMobileRepository->findBy(['telephone' => $telephone]);

        if (count($userExit) != 0) {
            return new Response("Ce numéro de téléphone est déjà utilisé", 400);
        }

        if ($email != '' || $email !=  null) {
            $userEmailExist = $userMobileRepository->findBy(['email' => $email]);
            if (count($userEmailExist) != 0) {
                return new Response("Cet e-mail est déjà utilisé par un autre utilisateur", 400);
            }
        }

        // $email = $email != '' ? $data['email'] : $data['prenom'] . "." . $data['nom'] . $data['idProfil'] . "@pdeps.sn";
        $email = $email != '' ? $data['email'] : '';

        $enabled = $data['enabled'];

        $profil = $pr->findOneBy(['id' => $data['idProfil']]);
        $departement =  $dr->findOneBy(['id' => $data['idDepartement']]);

        $region = $rr->findOneBy(['id' => $data['idRegion']]);
        $status = $str->findOneBy(['id' => $data['idStatus']]);

        $hasLaiteries = false;
        if (array_key_exists("hasLaiteries", $data)) {
            if ($data['hasLaiteries']) {
                $hasLaiteries = $data['hasLaiteries'];
            }
        }

        if (!$departement) {
            return new Response("Département  non valide", 400);
        }
        if (!$region) {
            return new Response("Région non valide", 400);
        }
        if (!$profil) {
            return new Response("Profil non valide", 400);
        }
        if (!$status) {
            return new Response("Statut non valide", 400);
        }

        try {
            $user = new UserMobile();
            $user->setPrenom($data['prenom']);
            $user->setNom($data['nom']);
            $user->setTelephone($data['telephone']);
            $user->setAdresse($data['adresse']);
            $user->setSexe($data['sexe']);
            $user->setPassword($data['password']);
            $user->setUuid($data['uuid']);
            $user->setLocalite($data['localite']);
            $user->setPassword($data['password']);
            $user->setRegion($region);
            $user->setStatus($status);
            $user->setDepartement($departement);
            $user->setProfil($profil);
            $user->setHasLaiteries($hasLaiteries);
            $user->setEnabled($enabled);
            $user->setEmail($email);


            $this->entityManager->persist($user);
            $this->entityManager->flush();
            try {
                $sms = $modelSmsRepository->findOneBy(['code' => 'CPTE_CREER']);
                if ($sms) {
                    $message = $sms->getMessage();
                    if ($sms->getParametre() != null && count($sms->getParametre()) != 0) {

                        $parametres = $sms->getParametre();
                        foreach ($parametres as $value) {
                            if ($value != '' && array_key_exists($value, $data)) {
                                $message = str_replace("[" . $value . "]", $data[$value], $message);
                            }
                        }
                    }
                }
                $response = $orangeSMSService->sendSMS($data['telephone'], $message);
                return new Response(" L'inscription s'est bien passée. Vos identifiants vient d'etre envoyé au " . $data['telephone'], 201);
            } catch (\Throwable $th) {
                return new Response("L'inscription a échouée. Merci de Réessayer ou contacter l'administeur de la plateforme " . $th->getMessage(), 400);
            }
        } catch (\Throwable $th) {
            return new Response("L'inscription a échouée. Merci de Réessayer ou contacter l'administeur de la plateforme " . $th->getMessage(), 400);
        }
    }

    /**
     * @Route("/api/user_mobiles/update", name="app_user_mobile_update",methods={"POST"})
     * @param Request $request
     */
    public function updateUserMobile(
        Request $request,
        UserMobileRepository $userMobileRepository,
        StatusRepository $str,
        ProfilsRepository $pr,
        RegionRepository $rr,
        DepartementRepository $dr,
        EntityManagerInterface $em
    ): ?Response {
        try {
            $data = json_decode($request->getContent(), true);
            $id = $data['id'];
            $email = $data['email'];
            $prenom = $data['prenom'];
            $nom = $data['nom'];
            $adresse = $data['adresse'];
            $telephone = $data['telephone'];


            $userExit = $userMobileRepository->findBy(['telephone' => $telephone]);

            if (count($userExit) != 0) {
                foreach ($userExit as $value) {
                    if ($id != $value->getId()) {
                        return new Response("Ce numéro de téléphone est déjà utilisé", 400);
                    }
                }
            }
            if ($email) {
                $userEmailExist = $userMobileRepository->findBy(['email' => $email]);

                if (count($userEmailExist) != 0) {
                    foreach ($userEmailExist as $value) {
                        if ($id != $value->getId()) {
                            return new Response("Cet e-mail est déjà utilisé par un autre utilisateur", 400);
                        }
                    }
                }
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

            $user = new UserMobile();
            $user = $userMobileRepository->findOneBy(["id" =>  $id]);
            if ($user == null) {
                return new JsonResponse("Cet Utilisateur n'existe pas ", 400);
            }
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

            return new JsonResponse($user->asArray(), 200);
        } catch (\Exception $e) {
            return new JsonResponse("Mise à jour non effectué $e ", 400);
        }
    }


    //verifier si le user est bloquer ou pas 
    /**
     * @Route("/api/user_mobiles/state/{id}", name="app_user_mobile_state",methods={"GET"})
     */
    public function getStateUserMobile(int $id, UserMobileRepository $userMobileRepository): JsonResponse
    {
        $user = $userMobileRepository->find($id);

        $statut = $user->getStatus();
        if (($statut->getNom() == "BLOCKE" || $statut->getId() == "2") ||  ($statut->getNom() == "ATTENTE" || $statut->getId() == "3")) {
            $r = $statut->getId() == 2 ?  $statut->getNom() : "en ATTENTE";
            return new JsonResponse(['blocked' => "Votre compte est " . $r], 200);
        }
        return new JsonResponse(['blocked' => 1], 200);
    }

    //mot de passe oublié
    /**
     * @Route("/api/user_mobiles/ressetPassword", name="app_user_mobile_reset_password",methods={"POST"})
     */
    public function ResetPasswordUserMobile(
        Request $request,
        ModelSmsRepository $modelSmsRepository,
        UserMobileRepository $userMobileRepository,
        OrangeSMSService  $orangeSMSService
    ) {

        $data = json_decode($request->getContent(), true);

        $email = $data["email"] ? $data["email"] : null;
        $prenom = $data["prenom"];
        $nom = $data["nom"];
        $telephone = $data["telephone"];
        $critere = ["email" => $email, "prenom" => $prenom, "nom" => $nom, "telephone" => $telephone];
        $user = $userMobileRepository->findOneBy($critere);
        if (!$user) {
            return new JsonResponse("Compte avec ces identifiants n'existe pas . \n Merci de contacter l'administrateur ", 200);
        }

        $statut = $user->getStatus();
        if (($statut->getNom() == "BLOCKE" || $statut->getId() == "2") ||  ($statut->getNom() == "ATTENTE" || $statut->getId() == "3")) {
            $r = $statut->getId() == 2 ?  $statut->getNom() : "en ATTENTE";

            $sms = $modelSmsRepository->findOneBy(['code' => 'CPTE_BLOQUE']);
            if ($sms) {
                $message = $sms->getMessage();
                if ($sms->getParametre() != null && count($sms->getParametre()) != 0) {
                    $parametres = $sms->getParametre();
                    foreach ($parametres as $value) {
                        if ($value != '' && array_key_exists($value, $data)) {
                            $message = str_replace("[" . $value . "]", $data[$value], $message);
                        }
                    }
                }
                $response = $orangeSMSService->sendSMS($user->getTelephone(), $message);
            }
            // $orangeSMSService->sendSMS($user->getTelephone(), "Nous sommes désolés de vous informer que votre compte est en " . $r . " en raison de violations de nos conditions d'utilisation.\nVeuillez contacter notre équipe d'assistance pour plus d'informations .");
            return new JsonResponse("Nous sommes désolés de vous informer que votre compte est en " . $r . " en raison de violations de nos conditions d'utilisation.\nVeuillez contacter notre équipe d'assistance pour plus d'informations .", 200);
        }

        $sms = $modelSmsRepository->findOneBy(['code' => 'IDENT_USERS']);
        if ($sms) {
            $message = $sms->getMessage();
            if ($sms->getParametre() != null && count($sms->getParametre()) != 0) {
                $parametres = $sms->getParametre();
                foreach ($parametres as $value) {
                    if ($value != '' && array_key_exists($value, $data)) {
                        $message = str_replace("[" . $value . "]", $data[$value], $message);
                    }
                    if ($value == "password") {
                        $message = str_replace("[" . $value . "]", $user->getPassword(), $message);
                    }
                }
            }
            $response = $orangeSMSService->sendSMS($user->getTelephone(), $message);
        }
        // $orangeSMSService->sendSMS($user->getTelephone(), "Vos identifiants de connexion sont les suivants : \nNom d'utilisateur : " . $user->getTelephone() . "\nMot de passe : " . $user->getPassword());
        return new JsonResponse("Vos identifiants vient d'etre envoyé au " . $user->getTelephone(), 200);
    }


    //------------------

    public function createUserMobile2(
        Request $request,
        UserMobileRepository $userMobileRepository,
        OrangeSMSService  $orangeSMSService,
        StatusRepository $str,
        RegionRepository $rr,
        ProfilsRepository $pr,
        DepartementRepository $dr,
        ModelSmsRepository $modelSmsRepository
    ): ?Response {
        try {

            $data = json_decode($request->getContent(), true);
            $email = $data['email'];
            $telephone = $data['telephone'];
            $userExit = $userMobileRepository->findBy(['telephone' => $telephone]);

            if (count($userExit) != 0) {
                return new Response("Ce numéro de téléphone est déjà utilisé", 400);
            }
            if ($email != '' || $email !=  null) {
                $userEmailExist = $userMobileRepository->findBy(['email' => $email]);
                if (count($userEmailExist) != 0) {
                    return new Response("Cet e-mail est déjà utilisé par un autre utilisateur", 400);
                }
            }

            $hasLaiteries = '0';
            if (array_key_exists("hasLaiteries", $data)) {
                if ($data['hasLaiteries']) {
                    $hasLaiteries = '1';
                }
            } else {
                $hasLaiteries = '0';
            }

            $email = $email != '' ? $data['email'] : $data['prenom'] . "." . $data['nom'] . $data['idProfil'] . "@pdeps.sn";
            // dd($email);

            $enabled = $data['enabled'] == true ? '1' : '0';

            // dd($enabled);


            $profil = $pr->findOneBy(['id' => $data['idProfil']]);
            $departement =  $dr->findOneBy(['id' => $data['idDepartement']]);
            $region = $rr->findOneBy(['id' => $data['idRegion']]);
            $status = $str->findOneBy(['id' => $data['idStatus']]);

            if (!$departement) {
                return new Response("Département  non valide", 400);
            }
            if (!$region) {
                return new Response("Région non valide", 400);
            }
            if (!$profil) {
                return new Response("Profil non valide", 400);
            }
            if (!$status) {
                return new Response("Profil non valide", 400);
            }

            try {
                $user = new UserMobile();
                $user->setPrenom($data['prenom']);
                $user->setNom($data['nom']);
                $user->setTelephone($data['telephone']);
                $user->setAdresse($data['adresse']);
                $user->setSexe($data['sexe']);
                $user->setPassword($data['password']);
                $user->setUuid($data['uuid']);
                $user->setLocalite($data['localite']);
                $user->setPassword($data['password']);
                $user->setRegion($region);
                $user->setStatus($status);
                $user->setDepartement($departement);
                $user->setProfil($profil);
                $user->setHasLaiteries(false);
                $user->setEnabled($enabled);
                $user->setEmail($email);

                // dd($user);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $sms = $modelSmsRepository->findOneBy(['code' => 'CPTE_CREER']);
                if ($sms) {

                    $message = $sms->getMessage();
                    if ($sms->getParametre() != null && count($sms->getParametre()) != 0) {

                        $parametres = $sms->getParametre();
                        foreach ($parametres as $value) {
                            if ($value != '' && array_key_exists($value, $data)) {
                                $message = str_replace("[" . $value . "]", $data[$value], $message);
                            }
                        }
                    }
                }

                $response = $orangeSMSService->sendSMS($data['telephone'], $message);
                return new JsonResponse(" L'inscription s'est bien passée \n Vos identifiants vient d'etre envoyé au " . $data['telephone'], 201);
            } catch (\Throwable $th) {
                dd($th);
            }


            $req = "INSERT INTO `simlait_user_mobiles` (roles, email, prenom, nom, telephone, adresse, sexe, enabled, password, region_id, departement_id, uuid, has_laiteries, profil_id, localite, status_id) VALUES ( '" .
                '["ROLE_USER"]' . "'  , '" .
                $email . "' ,'" .
                $data['prenom'] . "', '" .
                $data['nom'] .   "', '" .
                $data['telephone'] . "','" .
                $data['adresse'] . "', '" .
                $data['sexe'] . "' , '" .
                $enabled .  "' , '" .
                $data['password']  . "' , '" .
                $data['idRegion'] . "', '" .
                $data['idDepartement'] . "', '" .
                $data['uuid'] . "', " .
                $hasLaiteries . ", '" .
                $data['idProfil'] . "','" .
                $data['localite'] . "', '" .
                $data['idStatus'] . "')";

            try {

                $connT = $this->entityManager->getConnection();
                $stmtT = $connT->prepare($req);
                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative();

                $sms = $modelSmsRepository->findOneBy(['code' => 'CPTE_CREER']);
                if ($sms) {

                    $message = $sms->getMessage();
                    if ($sms->getParametre() != null && count($sms->getParametre()) != 0) {

                        $parametres = $sms->getParametre();
                        foreach ($parametres as $value) {
                            if ($value != '' && array_key_exists($value, $data)) {
                                $message = str_replace("[" . $value . "]", $data[$value], $message);
                            }
                        }
                    }

                    $response = $orangeSMSService->sendSMS($data['telephone'], $message);
                }
                // $orangeSMSService->sendSMS($data['telephone'], "Cher utilisateur, nous sommes heureux de vous informer que votre compte a été créé avec succès.\nVos identifiants de connexion sont les suivants : \nNom d'utilisateur : " . $data['telephone'] . "\nMot de passe : " . $data['password'] . "\nBonne utilisation ");

                return new JsonResponse(" L'inscription s'est bien passée \n Vos identifiants vient d'etre envoyé au " . $data['telephone'], 201);
            } catch (\Throwable $th) {
                throw $th;
            }
        } catch (\Exception $e) {
            return new Response("L'inscription a échouée. Merci de Réessayer ou contacter l'administeur de la plateforme " . $e->getMessage(), 400);
        }
    }




    /**
     * @Route("/api/user_mobilesListe", name="app_getLaiterie_user_liste",methods={"GET"})
     */
    public function getUserListe(UserMobileRepository $repo, EntityManagerInterface $em): ?Response
    {
        $users = $repo->findAllDesc();
        // $resultats = [];
        // foreach ($users as $m) {
        //     $resultats[] = $m->asArrayCollecte();
        // }

        $resultats = [];
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('u')
            ->addSelect('COUNT(c.id) as total_collectes')
            ->addSelect('SUM(CASE WHEN c.isCertified = 1 THEN 1 ELSE 0 END) as nombre_collectes_certifiees')
            ->addSelect('SUM(CASE WHEN c.isCertified = 0 AND c.toCorrect = 0 AND c.isDeleted = 0 THEN 1 ELSE 0 END) as nombre_collectes_non_certifiees')
            ->addSelect('SUM(CASE WHEN c.toCorrect = 1 THEN 1 ELSE 0 END) as nombre_collectes_a_corriger')
            ->addSelect('SUM(CASE WHEN c.isDeleted = 1 THEN 1 ELSE 0 END) as nombre_collectes_supprimees')
            ->from(UserMobile::class, 'u')
            ->leftJoin('App\Entity\Collecte', 'c', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.user = u.id')
            ->groupBy('u.id')
            ->orderBy('u.id', 'DESC');

        $results = $queryBuilder->getQuery()->getResult();

        $resultats =  [];
        foreach ($results as $key => $user) {
            $uniteData = $user[0]->asArrayCollecte();
            $uniteData['nombre_collectes_certifiees'] =  $user['nombre_collectes_certifiees'];
            $uniteData['nombre_collectes_non_certifiees'] = $user['nombre_collectes_non_certifiees'];
            $uniteData['nombre_collectes_a_corriger'] =  $user['nombre_collectes_a_corriger'];
            $uniteData['total_collectes'] = $user['total_collectes'];
            $uniteData['nombre_collectes_supprimees'] =  $user['nombre_collectes_supprimees'];
            $uniteData['total_collectes'] =  $user['total_collectes'];
            //
            $pourcentageCertifiees = ($user['total_collectes'] !== 0) ? ($user['nombre_collectes_certifiees'] / $user['total_collectes']) * 100 : 0;
            $pourcentageNonCertifiees = ($user['total_collectes'] !== 0) ? ($user['nombre_collectes_non_certifiees'] / $user['total_collectes']) * 100 : 0;
            $pourcentageACorriger = ($user['total_collectes'] !== 0) ? ($user['nombre_collectes_a_corriger'] / $user['total_collectes']) * 100 : 0;
            $pourcentageSupprimees = ($user['total_collectes'] !== 0) ? ($user['nombre_collectes_supprimees'] / $user['total_collectes']) * 100 : 0;
            //
            $uniteData['pourcentageCertifiees'] = $pourcentageCertifiees;
            $uniteData['pourcentageNonCertifiees'] =   $pourcentageNonCertifiees;
            $uniteData['pourcentageACorriger'] =  $pourcentageACorriger;
            $uniteData['pourcentageSupprimees'] =  $pourcentageSupprimees;
            $resultats[$key] =  $uniteData;
        }

        return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
    }
}
