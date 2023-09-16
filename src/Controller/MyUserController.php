<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ModelSmsRepository;
use App\Repository\ParametrageMobileRepository;
use App\Repository\UnitesRepository;
use App\Repository\UserMobileRepository;
use App\Repository\UserRepository;
use App\service\OrangeSMSService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Mailer\MailerInterface;


class MyUserController extends AbstractController
{


    private $entityManager;
    private $passwordEncoder;
    private $userRepo;
    private $jwtManager;
    private $tokenStorageInterface;
    private $encoderFactory;
    public function __construct(UserRepository $userRepo,  EntityManagerInterface $entityManager, TokenStorageInterface  $tokenStorageInterface, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager)
    {
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->passwordEncoder = $passwordHasher;
        $this->userRepo = $userRepo;
    }

    /**
     * @Route("/users/register",name="create_account", methods={"POST"})
     */
    public function register(
        MailerInterface $mailer,
        UserRepository $userRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        OrangeSMSService  $orangeSMSService,
        ModelSmsRepository $modelSmsRepository
    ): ?Response {

        $data  = json_decode($request->getContent(), true);
        $user = new User();
        $user->setFirstName($data['firstName']);
        $user->setEnabled($data['enabled']);
        $user->setAdresse($data['adresse']);
        $user->setSexe($data['sexe']);
        $date = new \DateTime('now');
        $datei = DateTimeImmutable::createFromMutable($date);
        $user->setLastActivityAt($datei);
        $user->setLastName($data['lastName']);
        $user->setZoneIntervention($data['zoneIntervention']);
        // $user->setAvatar();

        $user->setRoles($data['roles']);
        $user->setStatus($data['status']);
        $user->setIsActiveNow(false);

        $userExit = $userRepository->findOneBy(['phone' => $data['phone']]);
        if ($userExit) {
            return new Response("Ce numéro de téléphone est déjà utilisé", 400);
        }

        $user->setPhone($data['phone']);

        if ($data['email'] != null) {
            $users = $userRepository->findBy(['email' => $data['email']]);

            if (count($users) != 0) {
                return new JsonResponse("Cet e-mail est déjà utilisé par un autre utilisateur", 400);
            } else {
                $user->setEmail($data['email']);
            }
        }

        $hashedPassword = $this->passwordEncoder->hashPassword(
            $user,
            $data['password']
        );
        $user->setPassword($hashedPassword);


        $entityManager->persist($user);
        $entityManager->flush();

        $usersAdmin = $userRepository->findBy(['roles' => "ROLE_ADMIN"]);

        if (count($usersAdmin) != 0) {

            if ($user->getEmail() != null) {
                foreach ($usersAdmin as $u) {
                    $email = (new TemplatedEmail())
                        ->from('pdepssimlait@gmail.com')
                        ->to($u->getEmail())
                        ->cc($user->getEmail())
                        ->subject('Ouverture de Compte')
                        ->htmlTemplate('main/mailOuvertureCompte.html.twig')
                        ->context([
                            'user' => $user,
                        ]);
                    $mailer->send($email);
                }
            } else {
                foreach ($usersAdmin as $u) {
                    $email = (new TemplatedEmail())
                        ->from('pdepssimlait@gmail.com')
                        ->to($u->getEmail())
                        ->subject('Ouverture de Compte')
                        ->htmlTemplate('main/mailOuvertureCompte.html.twig')
                        ->context([
                            'user' => $user,
                        ]);
                    $mailer->send($email);
                }
            }
        } else {
            if ($user->getEmail() != null) {

                $email = (new TemplatedEmail())
                    ->from('pdepssimlait@gmail.com')
                    ->to("pdepssimlait@gmail.com")
                    ->cc($user->getEmail())
                    ->subject('Ouverture de Compte')
                    ->htmlTemplate('main/mailOuvertureCompte.html.twig')
                    ->context([
                        'user' => $user,
                    ]);
                $mailer->send($email);
            } else {
                $email = (new TemplatedEmail())
                    ->from('pdepssimlait@gmail.com')
                    ->to("pdepssimlait@gmail.com")
                    ->subject('Ouverture de Compte')
                    ->htmlTemplate('main/mailOuvertureCompte.html.twig')
                    ->context([
                        'user' => $user,
                    ]);
                $mailer->send($email);
            }
        }


        $sms = $modelSmsRepository->findOneBy(['code' => 'CPTE_CREER']);
        if ($sms) {

            $message = $sms->getMessage();
            if ($sms->getParametre() != null && count($sms->getParametre()) != 0) {
                $parametres = $sms->getParametre();
                foreach ($parametres as $value) {
                    if ($value != '') {
                        $message = str_replace("[" . $value . "]", $data[$value], $message);
                    }
                }
            }
            $response = $orangeSMSService->sendSMS($user->getPhone(), $message);
        }
        //  $orangeSMSService->sendSMS($user->getPhone(), "Félicitations ! Votre compte a été créé avec succès !\n Veuillez patienter pendant que nous procédons à l'activation de votre compte.");
        return new JsonResponse($user->asArray(), 201);
    }

    /**
     * @Route("/api/users/profil", name="user_profil", methods={"POST"} )
     */
    public function updateProfil(
        UserRepository $userRepository,
        Request $request,
        UserRepository $repo,
        EntityManagerInterface  $entityManager
    ) {

        $data  = json_decode($request->getContent(), true);
        $user = $repo->find($data['id']);
        $email = $data['email'];
        if ($email != "") {
            $emails = $repo->findBy(['email' => $email]);

            $temoins = false;
            foreach ($emails as $e) {
                if ($e->getEmail()  == $email && $e->getId() != $user->getId()) {
                    $temoins = true;
                }
            }
            if ($temoins) {
                return new JsonResponse("Cet e-mail est déjà utilisé par un autre utilisateur", 400);
            }
            $user->setEmail($email);
        } else {
            $user->setEmail(null);
        }

        // dd($user);
        $userExit = $userRepository->findOneBy(['phone' => $data['phone']]);
        if ($userExit && $user->getId() != $userExit->getId()) {
            return new Response("Ce numéro de téléphone est déjà utilisé", 400);
        }


        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setPhone($data['phone']);
        $user->setAdresse($data['adresse']);
        $user->setIsActiveNow($data['isActiveNow']);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse($user->asArray(), 200);
    }


    // se connecter avec email pour l'utilisateur mobile
    /**
     * @Route("/api/findUserWeb/{email}", name="app_get_user_web",methods={"GET"})
     */
    public function getOneUserWeb(
        UserRepository $repo,
        String $email
    ): ?Response {
        try {
            $criteria1 = ['email' => $email];
            $criteria2 = ['phone' => $email];
            $user = $repo->findOneBy($criteria1);
            if ($user ==  null) {
                // throw  new  NotFoundHttpException("user avec l'email $email non trouvé");
                $user = $repo->findOneBy($criteria2);
                if ($user == null) throw  new  NotFoundHttpException("user avec l'email ou telephone non trouvé");
            }
            return new JsonResponse($user, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {

            return new JsonResponse(['err' => $e->getMessage()], 400);
        }
    }



    /**
     * @Route("/api/users/update", name="user_update", methods={"POST"} )
     */
    public function update(MailerInterface $mailer, UserRepository $userRepository, Request $request, UserRepository $repo, EntityManagerInterface  $entityManager)
    {
        $data  = json_decode($request->getContent(), true);

        $user = $repo->find($data['id']);
        $etat = $user->getEnabled();
        $status = $user->getStatus();
        $email = $data['email'] || null;
        $user->setFirstName($data['firstName']);

        $user->setEnabled($data['enabled']);
        $user->setLastName($data['lastName']);

        $user->setPhone($data['phone']);
        $userExit = $userRepository->findOneBy(['phone' => $data['phone']]);
        if ($userExit) {
            return new Response("Numéro de téléphone existe déjà", 400);
        }

        if ($user->getEmail() != null) {
            $users = $repo->findBy(['email' => $user->getEmail()]);
            if ($users) {
                return new JsonResponse(["adresse email existe deja "], 400);
            }
        }
        $user->setEmail($email);
        $user->setStatus($data['status']);
        $user->setRoles($data['roles']);

        if ($user->getEnabled() == false) {
            $user->setStatus("BLOCKE");
        }
        $entityManager->persist($user);
        $entityManager->flush();

        if ($user && $user->getEnabled() != $etat && $user->getEmail() != null) {
            $email = (new TemplatedEmail())
                ->from('simlait@pdefs.com')
                ->to($user->getEmail())
                ->subject('Activation  Compte')
                ->htmlTemplate('main/mailActivationCompte.html.twig')
                ->context([
                    'user' => $user,
                ]);
            $mailer->send($email);
        }
        if ($user && $status == $user->getStatus()  && $user->getEmail() != null) {
            $email = (new TemplatedEmail())
                ->from('simlait@pdefs.com')
                ->to($user->getEmail())
                ->subject('Statut de votre Compte')
                ->htmlTemplate('main/mailStatutCompte.html.twig')
                ->context([
                    'user' => $user,
                ]);
            $mailer->send($email);
        }

        return new JsonResponse("utilisateur mise à jour  avec succes ", 200);
    }


    /**
     * @Route("/api/usersConnected",name="users_connected", methods={"GET"})
     */
    public function userConnected(SerializerInterface $serializer)
    {
        $user =  $this->tokenStorageInterface->getToken()->getUser();
        if ($user instanceof User) {
            $user = $this->userRepo->find($user->getId());
            $user->setIsActiveNow(true);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        $user = $serializer->serialize($user, 'json');
        $response = new Response($user);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    public function fn_mesLaiteries(
        UnitesRepository $repo
    ) {
        $owner = $this->fn_me();
        if (null == $owner) {
            return null;
        }

        return  $repo->findBy(['owner' => $owner]);
    }

    public function fn_me(): ?User
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

    public function find_user_email(
        Request $request,
        UserMobileRepository $repo,
        ParametrageMobileRepository $parametrageMobileRepo
    ): ?Response {

        $pm = $parametrageMobileRepo->find(1);
        $message = "";
        if ($pm) {
            $message =  "Identifiant ou de mot de passe incorrect , Merci de réessayer. Si Vous avez oublié votre mot de passe ,merci de contacter l'administrateur au " . $pm->getEmailSupport() . " ou sur le " . $pm->getContactSupport();
        } else {
            $message = "Identifiant ou de mot de passe incorrect";
        }
        try {
            $data = json_decode($request->getContent(), true);
            $password = $data['password'];
            $email = $data['email'];
            $criteria = ['password' => $password, 'email' => $email];

            $user = $repo->findOneBy($criteria);
            if ($user ==  null) {
                return new Response($message, 400, ["Content-Type" => "application/json"]);
            }
            return new JsonResponse($user->asArray(), 200);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 400);
        }
    }


    // se connecter avec telephone pour l'utilisateur mobile
    /**
     * @Route("/api/user_mobiles/findByTel", name="app_get_user_tel",methods={"POST"})
     */
    public function getOneUserMobileByTel(
        Request $request,
        UserMobileRepository $repo,
        ParametrageMobileRepository $parametrageMobileRepo
    ): ?Response {

        $pm = $parametrageMobileRepo->find(1);
        $message = "";
        if ($pm) {
            $message =  "Identifiant ou de mot de passe incorrect, Merci de réessayer. Si Vous avez oublié votre mot de passe ,merci de contacter l'administrateur au " . $pm->getEmailSupport() . " ou sur le " . $pm->getContactSupport();
        } else {
            $message = "Identifiant ou de mot de passe incorrect";
        }
        try {
            $data = json_decode($request->getContent(), true);
            $password = $data['password'];
            $tel = $data['telephone'];
            $criteria = ['password' => $password, 'telephone' => $tel];

            $user = $repo->findOneBy($criteria);
            if ($user ==  null) {
                return new Response($message, 400, ["Content-Type" => "application/json"]);
            }
            return new JsonResponse($user->asArray(), 200);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 400, ["Content-Type" => "application/json"]);
        }
        return new JsonResponse(false, 400);
    }


    // se connecter avec email pour l'utilisateur mobile
    /**
     * @Route("/api/findUser/{email}", name="app_get_user",methods={"GET"})
     */
    public function getOneUserMobile(
        UserMobileRepository $repo,

        LoggerInterface $logger
    ): ?Response {

        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $email = $decodedJwtToken['email'];

        try {
            // $user = $repo->findOneBy(['email'=> $content['email'] ]);
            $criteria = ['email' => $email];
            $user = $repo->findOneBy($criteria);
            if ($user ==  null) {
                $logger->critical("Email non valide", ['email' => $email]);
                throw  new  NotFoundHttpException(" user avec l'email $email non trouvé");
            }
            return new JsonResponse($user->asArray(), 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 400);
        }
    }


    /**
     * @Route("/api/users/updateAvatar",  name="app_upload_profil", methods={"POST"} )
     */
    public function uploadImage(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): ?Response
    {
        // define('UPLOAD_DIR', 'avatars/');

        $datas = json_decode($request->getContent(), true);
        $url =  $datas['image'];
        $idUser = $datas['idUser'];

        $user = $userRepository->find($idUser);
        try {

            $user->setAvatar($url);
            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse($user->asArray(), 200);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400);
        }
    }

    /**
     * @Route("/api/users/isSuperadmin", name="user_super_admin", methods={"POST"} )
     */
    public function isSuperAdmin(UserRepository $userRepository, Request $request)
    {

        $data = json_decode($request->getcontent(), true);
        $user = $userRepository->find($data['id']);
        // Décryptage
        $pass = $data['pass'];
        $hashedPassword = $user->getPass();
        $isPass = hash('sha256', $pass) === $hashedPassword;
        if (!$isPass)  return new JsonResponse(false, 200);
        return  new JsonResponse($isPass, 200);
    }

    /**
     * @Route("/api/users/setPassSuperAdmin", name="user_set_pass_super_admin", methods={"POST"} )
     */
    public function SetPassSuperAdmin(EntityManagerInterface $entityManager, UserRepository $userRepository, Request $request)
    {

        $data = json_decode($request->getcontent(), true);
        $user = $userRepository->find($data['id']);
        $pass = $data['pass'];
        $hashedPass = hash('sha256', $pass);

        $user->setPass($hashedPass);
        $entityManager->persist($user);
        $entityManager->flush();

        return  new JsonResponse(true, 200);
    }

    /**
     * @Route("/api/users/getOldPass", name="user_old_pass_super_admin", methods={"POST"} )
     */
    public function getOldPass(UserRepository $userRepository, Request $request)
    {
        $data = json_decode($request->getcontent(), true);
        $user = $userRepository->find($data['id']);
        $hashedPassword = $user->getPass();
        return  new JsonResponse($hashedPassword, 200);
    }

    /**
     * @Route("/api/users/createPass", name="user_create_pass_super_admin", methods={"POST"} )
     */
    public function createHashPass(Request $request)
    {

        $data = json_decode($request->getcontent(), true);
        $pass = $data['pass'];
        $hashedPass = hash('sha256', $pass);
        return  new JsonResponse($hashedPass, 200);
    }
}
