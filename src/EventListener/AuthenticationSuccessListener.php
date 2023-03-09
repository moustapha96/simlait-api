<?php

namespace App\EventListener;

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

        if (!$user instanceof UserInterface) {
            return;
        }

        $data['user'] = array(
            'roles' => $user->getRoles(),
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'phone' => $user->getPhone(),
            'enabled' => $user->getEnabled(),
            'isActiveNow' => $user->getIsActiveNow(),
            'lastActivityAt' => $user->getLastActivityAt(),
            'pass' => $user->getPass(),
            'sexe' => $user->getSexe(),
            'status' => $user->getStatus(),
            'adresse' => $user->getAdresse(),
            'sent' => $user->getSent(),
            'received' => $user->getReceived(),
            'avatar' => $user->getAvatar(),
        );

        $event->setData($data);
    }
}
