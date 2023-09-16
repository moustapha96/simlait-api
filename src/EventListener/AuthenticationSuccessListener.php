<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;


class AuthenticationSuccessListener
{

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof User || !$user instanceof UserInterface) {
            return;
        }

        $data['user'] = array(
            'roles' => $user->getRoles(),
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'phone' => $user->getPhone(),
            'enabled' => $user->getEnabled(),
            'isActiveNow' => $user->getIsActiveNow(),
            'lastActivityAt' => $user->getLastActivityAt(),
            'sexe' => $user->getSexe(),
            'status' => $user->getStatus(),
            'adresse' => $user->getAdresse(),
            'sent' => $user->getSent(),
            'received' => $user->getReceived(),
            'departement' => $user->getDepartement()->asArrayUser() ?  $user->getDepartement()->asArrayUser() : null,
            'avatar' => $user->getAvatar(),
            'zoneIntervention' => $user->getZoneIntervention(),
        );

        $event->setData($data);
    }
}
