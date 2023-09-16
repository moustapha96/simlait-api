<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class LoginController
{

    // private $jwtManager;

    // public function __construct(JWTTokenManagerInterface $jwtManager)
    // {
    //     $this->jwtManager = $jwtManager;
    // }

    // public function __invoke(Request $request)
    // {
    //     /** @var UserInterface $user */
    //     $user = $this->getUser();


    //     // $user = $tokenStorage->getToken()->getUser();

    //     if (!$user instanceof User) {
    //         throw new BadCredentialsException();
    //     }

    //     $data = [
    //         'id' => $user->getId(),
    //         'username' => $user->getUsername(),
    //         'email' => $user->getEmail(),
    //         'roles' => $user->getRoles(),
    //         'firstName' => $user->getFirstName(),
    //         'lastName' => $user->getLastName(),
    //         'phone' => $user->getPhone(),
    //         'enabled' => $user->getEnabled(),
    //         'isActiveNow' => $user->getIsActiveNow(),
    //         'lastActivityAt' => $user->getLastActivityAt(),
    //         'sexe' => $user->getSexe(),
    //         'status' => $user->getStatus(),
    //         'adresse' => $user->getAdresse(),
    //         'sent' => $user->getSent(),
    //         'received' => $user->getReceived(),
    //         'avatar' => $user->getAvatar(),
    //     ];

    //     $token = $this->jwtManager->create($user, $data);

    //     return ['token' => $token];
    // }
}
