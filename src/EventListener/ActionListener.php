<?php

namespace App\EventListener;

use App\Entity\Notification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\Event\RequestEvent;


use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Repository\ParametrageMobileRepository;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ActionListener  extends Event implements EventSubscriberInterface
{



    private EntityManagerInterface $entityManager;
    public $paramsRepo;
    public $notificationRepository;
    protected $tokenStorage;


    public function __construct(

        EntityManagerInterface $entityManager,
        TokenStorageInterface  $tokenStorage,
        ParametrageMobileRepository $paramsRepo,
        NotificationRepository $notificationRepository
    ) {

        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->paramsRepo = $paramsRepo;
        $this->notificationRepository = $notificationRepository;
    }


    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            // KernelEvents::RESPONSE => 'onKernelResponse',
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

    public function onKernelResponse(ResponseEvent $event)
    {
    }


    public function onKernelRequest(
        RequestEvent $event,

    ) {

        $methode = $event->getRequest()->getMethod();
        $path = $event->getRequest()->getPathInfo();
        $listeTables = ['zones', 'produits', 'departements', 'regions', 'conditionnements', 'profils'];


        if ($methode == "POST") {
            $resultat = explode("/", $path);
            $entity = $resultat[2];

            if (in_array($entity, $listeTables)) {
                $existingNotification = $this->entityManager->getRepository(Notification::class)->findOneBy([
                    'message' => "Un nouveau élément vient d'être ajouté dans " . $entity,
                    'date' => new \DateTime()
                ]);
                if (!$existingNotification) {
                    $noti = new Notification();
                    $date = new \DateTime();
                    $noti->setMessage("Un nouveau élément vient d'être ajouté dans " . $entity);
                    $noti->setTitre("Nouveau élément dans " . $entity);
                    $noti->setDate(new \DateTime());
                    $noti->setDateExpirat($date->modify('+7 day'));

                    $para = $this->paramsRepo->find(1);
                    $para->setMessage("Merci de metre à jour vos données " . $entity);
                    $para->isHasNotification(true);

                    $this->entityManager->persist($noti);
                    $this->entityManager->persist($para);
                    $this->entityManager->flush();
                } else {
                    // update existing notification expiration date
                    $existingNotification->setDateExpirat(new \DateTime('+7 day'));
                    $this->entityManager->flush();
                }
            }
        }
    }
}
