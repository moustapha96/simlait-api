<?php

namespace App\EventListener\Unites;

use App\Entity\Notification;
use App\Entity\ParametrageMobile;
use App\Repository\NotificationRepository;
use App\Repository\ParametrageMobileRepository;
use App\Repository\UnitesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Product\Event\ProductCreateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UniteEventSubscriber implements EventSubscriberInterface
{
    public $entityManager;
    public $paramsRepo;
    public $notificationRepository;


    public function __construct(
        EntityManagerInterface $entityManager,
        ParametrageMobileRepository $paramsRepo,
        NotificationRepository $notificationRepository
    ) {
        $this->entityManager = $entityManager;
        $this->paramsRepo = $paramsRepo;
        $this->notificationRepository = $notificationRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            UniteCreateEvent::NAME => 'onUniteCreation',
            UniteUpdateEvent::NAME => [
                ['onUniteCreation', 1],
                ['onUniteUpdation', 2],
            ],
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onUniteCreation(UniteCreateEvent $event)
    {

       
        $noti = new Notification();
        $date = new \DateTime();
        $noti->setMessage("une nouvelle unité vient d'etre enregistrer");
        $noti->setTitre("Nouvelle Unité");
        $noti->setDate(new \DateTime());
        $noti->setDateExpirat($date->modify('+7 day'));
        $this->entityManager->persist($noti);
        $this->entityManager->flush();

        $para = $this->paramsRepo->find(1);
        $para->isHasNotification(true);
        $this->entityManager->persist($para);
        $this->entityManager->flush();
    }

    public function onUniteUpdation(UniteUpdateEvent $event)
    {
        // write code to execute on product updation event
    }

    public function onKernelResponse(ResponseEvent  $event)
    {
        // write code to execute on in-built Kernel Response event
    }
}