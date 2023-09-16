<?php
// api/src/OpenApi/JwtDecorator.php

declare(strict_types=1);

namespace App\OpenApi;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class JwtPayloadListener
{


    public function onJwtCreated(JWTCreatedEvent $event)
    {
        $payload = $event->getData();
        $user =  $event->getUser();

        if ($user instanceof User) {
            $payload['phone'] = $user->getPhone();
            $payload['id'] = $user->getId();
        }

        if (!$payload['email'] &&  $user instanceof User) {
            $payload['email'] = $user->getPhone();
        }
        $event->setData($payload);
    }


    public function me(TokenStorageInterface $tokenStorage): JsonResponse
    {
        /** @var UserInterface $user */
        $user = $tokenStorage->getToken()->getUser();

        $data = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
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

        ];

        return new JsonResponse($data);
    }
}