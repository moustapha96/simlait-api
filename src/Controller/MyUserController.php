<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\LoggerRepository;
use App\Repository\UnitesRepository;
use App\Repository\UserMobileRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;


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
        // $this->encoderFactory = $encoderFactory;
    }

    /**
     * @Route("/users/register",name="create_account", methods={"POST"})
     */
    public function register(MailerInterface $mailer, Request $request, UserRepository $repo, EntityManagerInterface $entityManager): ?Response
    {
        $data  = json_decode($request->getContent(), true);
        $user = new User();
        $user->setFirstName($data['firstName']);
        $user->setEmail($data['email']);
        $user->setEnabled($data['enabled']);
        $date = new \DateTime($data['isActiveNow']);
        $datei = DateTimeImmutable::createFromMutable($date);
        $user->setLastActivityAt($datei);
        $user->setLastName($data['lastName']);
        $user->setAvatar($data['avatar']);
        $user->setPhone($data['phone']);
        $user->setRoles($data['roles']);
        $user->setStatus($data['status']);
        $user->setIsActiveNow(false);
        $hashedPassword = $this->passwordEncoder->hashPassword(
            $user,
            $data['password']
        );
        $user->setPassword($hashedPassword);
        $users = $repo->findBy(['email' => $user->getEmail()]);
        if ($users) {
            return new JsonResponse(["adresse email existe deja "], 500);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $usersAdmin = $repo->findBy(['roles' => "ROLE_ADMIN"]);

        if (count($usersAdmin) != 0) {
            foreach ($usersAdmin as $u) {
                $email = (new TemplatedEmail())
                    ->from('simlait@pdefs.com')
                    ->to($u->getEmail())
                    ->cc($user->getEmail())
                    ->bcc('khouma964@gmail.com')
                    ->subject('Ouverture de Compte')
                    ->htmlTemplate('main/mailOuvertureCompte.html.twig')
                    ->context([
                        'user' => $user,
                    ]);
                $mailer->send($email);
            }
        } else {
            $email = (new TemplatedEmail())
                ->from('simlait@pdefs.com')
                ->to("simlait-admin@pdefs.com")
                ->cc($user->getEmail())
                ->bcc('bcc@example.com')
                ->subject('Ouverture de Compte')
                ->htmlTemplate('main/mailOuvertureCompte.html.twig')
                ->context([
                    'user' => $user,
                ]);
            $mailer->send($email);
        }

        return new JsonResponse(["utilisateur créer avec succès "], 200);
    }

    /**
     * @Route("/api/users/profil", name="user_profil", methods={"POST"} )
     */
    public function updateProfil(MailerInterface $mailer, Request $request, UserRepository $repo, EntityManagerInterface  $entityManager)
    {

        $data  = json_decode($request->getContent(), true);
        $user = $repo->find($data['id']);
        $email = $data['email'];
        $emails = $repo->findBy(['email' => $data['email']]);

        $temoins = false;
        foreach ($emails as $e) {
            if ($e->getEmail()  == $email && $e->getId() != $user->getId()) {
                $temoins = true;
            }
        }
        if ($temoins) {
            return new JsonResponse(["Email deja existant "], 500);
        }

        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setPhone($data['phone']);
        $user->setAdresse($data['adresse']);
        $user->setIsActiveNow($data['isActiveNow']);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(["utilisateur mise a jour  avec succes "], 200);
    }

    /**
     * @Route("/api/findUserWeb/{email}", name="app_get_user_web",methods={"GET"})
     */
    public function getOneUserWeb(
        UserRepository $repo,
        String $email
    ): ?Response {
        try {
            $criteria = ['email' => $email];
            $user = $repo->findOneBy($criteria);
            if ($user ==  null) {
                throw  new  NotFoundHttpException(" user avec l'email $email non trouvé");
            }
            return new JsonResponse($user, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            $resultat =  ["RESULTAT", ['code' => 500, "err" => $e->getMessage()]];

            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }


    /**
     * @Route("/api/users/update", name="user_update", methods={"POST"} )
     */
    public function update(MailerInterface $mailer, Request $request, UserRepository $repo, EntityManagerInterface  $entityManager)
    {
        $data  = json_decode($request->getContent(), true);

        $user = $repo->find($data['id']);
        $etat = $user->getEnabled();
        $status = $user->getStatus();

        $user->setFirstName($data['firstName']);
        // $user->setEmail($data['email']);
        $user->setEnabled($data['enabled']);
        $user->setLastName($data['lastName']);
        $user->setPhone($data['phone']);
        $user->setStatus($data['status']);
        $user->setRoles($data['roles']);

        if ($user->getEnabled() == false) {
            $user->setStatus("BLOCKE");
        }
        $entityManager->persist($user);
        $entityManager->flush();

        if ($user && $user->getEnabled() != $etat) {
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
        if ($user && $status == $user->getStatus()) {
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

        return new JsonResponse(["utilisateur mise a jour  avec succes "], 200);
    }

    // /**
    //  * @Route("/api/usersConnected",name="users_connected", methods={"GET"})
    //  */
    // public function userConnected(SerializerInterface $serializer)
    // {
    //     dd();
    //     $user = $this->getUser();

    //     $users = $serializer->serialize($user, 'json');

    //     $response = new Response($users);

    //     $response->headers->set('Content-Type', 'application/json');
    //     return $response;
    // }

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
        LoggerRepository $repoLogger,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ): ?Response {

        // $logger->info("requette", ['requette' => $request]);
        try {
            $data = json_decode($request->getContent(), true);
            $password = $data['password'];
            $email = $data['email'];
            $criteria = ['password' => $password, 'email' => $email];

            $user = $repo->findOneBy($criteria);
            // $logger->info('User logged in', ['user' => $user]);
            if ($user ==  null) {
                // $logger->critical("Email non valide", ['email' => $email]);
                return new JsonResponse('Compte avec ces identifiants non trouvé', 400, ["Content-Type" => "application/json"]);
                // throw  new  NotFoundHttpException(" user avec l'email $email non trouvé");
            }
            return new JsonResponse($user->asArray(), 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 400);
        }
    }




    /**
     * @Route("/api/findUser/{email}", name="app_get_user",methods={"GET"})
     */
    public function getOneUserMobile(
        Request $request,
        UserMobileRepository $repo,
        LoggerRepository $repoLogger,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
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
            $resultat =  ["RESULTAT", ['code' => 400, "err" => $e->getMessage()]];

            return new JsonResponse(['err' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/api/users/updateAvatar",  name="app_upload_profil", methods={"POST"} )
     */
    public function uploadImage(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): ?Response
    {
        define('UPLOAD_DIR', 'avatars/');



        $datas = json_decode($request->getContent(), true);
        $url =  $datas['image'];
        $idUser = $datas['idUser'];

        $user = $userRepository->find($idUser);

        if (str_contains($url, ",")) {
            $po = strpos($url, ",");
            $avatar_base64 = substr($url,  $po + 1);
        }
        $data = base64_decode($avatar_base64);
        $file = UPLOAD_DIR . uniqid() . '.jpeg';
        $success = file_put_contents($file, $data);

        $type = pathinfo($file, PATHINFO_EXTENSION);
        $data = file_get_contents($file);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        try {

            $user->setAvatar($base64);

            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse("Avatar enregistrer avec succés !! ");
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
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
