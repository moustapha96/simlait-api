<?php


namespace App\EventListener;




use App\Entity\TableCounter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Exception;

class CreatedAtListener
{
    private $entityManager;
    public function __construct(

        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager =  $entityManager;
    }

    public function prePersist(LifecycleEventArgs $args)
    {

        $entity = $args->getEntity();
        $now = new \DateTimeImmutable();
        // Vérifiez si l'entité a une propriété "createdAt"
        if (property_exists($entity, 'createdAt')) {
            $entity->setCreatedAt($now);
        }
    }
}
