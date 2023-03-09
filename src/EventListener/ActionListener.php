<?php

namespace App\EventListener;

use App\Entity\Logger;
use App\Entity\Notification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;


use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Repository\ParametrageMobileRepository;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\EventDispatcher\Event;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ActionListener  extends Event implements EventSubscriberInterface
{


    private $logger;
    private EntityManagerInterface $entityManager;
    public $paramsRepo;
    public $notificationRepository;
    protected $tokenStorage;


    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        TokenStorageInterface  $tokenStorage,
        ParametrageMobileRepository $paramsRepo,
        NotificationRepository $notificationRepository
    ) {
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->paramsRepo = $paramsRepo;
        $this->notificationRepository = $notificationRepository;
    }



    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse',
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event)
    {

        if ($this->tokenStorage->getToken()) {
            $user_connected = $this->tokenStorage->getToken()->getUser();
            if ($user_connected instanceof User) {
                $user_connected->setLastActivityAt(new \DateTime());
                $user_connected->setIsActiveNow(true);
                $this->entityManager->persist($user_connected);
                $this->entityManager->flush();
            }
        }
    }

    public function onKernelResponse(ResponseEvent  $event)
    {

        $logger  = new Logger();
        $response =   $event->getResponse();
        $statusCode = $response->getStatusCode();
        $response_content = $response->getContent();
        $request = $event->getRequest();
        $method = $request->getMethod();
        $requestUri = $request->getRequestUri();
        $host = $request->headers->get('host');
        $request_content = json_decode($request->getContent(), true);

        $date = new \DateTime();
        $from_app = "";
        if ($this->tokenStorage->getToken()) {
            $user_connected = $this->tokenStorage->getToken()->getUser();
            if ($user_connected instanceof User) {
                $user_connected->setLastActivityAt(new \DateTime());
                $user_connected->setIsActiveNow(true);
            }

            if ($user_connected->getEmail()) {
                $email = $user_connected->getEmail();
                $from_app = "web";
            } else {
                $email = "";
                $from_app = "mobile";
            }
        } else {
            $email = "";
            $from_app = "mobile";
        }

        if ($request_content == null) {
            $logger->setRequestContent([]);
        } else {
            $logger->setRequestContent($request_content);
        }
        $logger->setFromApp($from_app);
        $logger->setDateRequest($date);
        $logger->setEmail($email);
        $logger->setResponseContent($response_content);
        $logger->setHost($host);
        $logger->setRequestUri($requestUri);
        $logger->setMethod($method);
        $logger->setStatutCode($statusCode);


        $filesystem = new Filesystem();
        $current_dir_path = getcwd();
        try {
            $new_dir_path = $current_dir_path . "/logger/log.txt";
            $filesystem->appendToFile($new_dir_path, $logger->asArray());
        } catch (IOExceptionInterface $exception) {
            echo "Error creating file at" . $exception->getPath();
        }
        // $this->entityManager->persist($logger);
        // $this->entityManager->flush();
    }

    public function onKernelRequest(
        RequestEvent $event,

    ) {
        $methode = $event->getRequest()->getMethod();
        $path = $event->getRequest()->getPathInfo();
        $listeExcepEntity = [
            "users", "notifications", "messages", "loggers",
            "collectes", "greetings", "parametrage_mobiles", "conditionnements_produits_unites",
            "unites_demande_suivis", "unites_demandes", "code_reset_passwords", "statuses", "data_formulaires", "data", "allTable", "getData",
            "verfiyCode", "createProfil", "authentication_token", "getProduitPlusCollecter", "getCollecteByZone", "getCollectesCertified", "getLastCollectes",
            "user_mobiles", "agregations", "unites_autres", "createProfil"
        ];
        if ($methode == "POST" || $methode == "PUT") {
            $resultat = explode("/", $path);
            $entity = $resultat[2];

            if (!in_array($entity, $listeExcepEntity)) {
                $noti = new Notification();
                $date = new \DateTime();
                $noti->setMessage("Un nouveau élément vien d'être ajouter dans " . $entity);
                $noti->setTitre("Nouveau élément dans " . $entity);
                $noti->setDate(new \DateTime());
                $noti->setDateExpirat($date->modify('+7 day'));

                $para = $this->paramsRepo->find(1);
                $para->setMessage("Merci de metre à jour vos données " . $entity);
                $para->isHasNotification(true);

                $this->entityManager->persist($noti);
                $this->entityManager->persist($para);
                $this->entityManager->flush();
            }
        }
    }


    public function onKernelException(ExceptionEvent $event)
    {
    }
}
