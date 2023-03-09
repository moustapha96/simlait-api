<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class LoginController
{


    public function __invoke(Request $request, UserRepository $userRepository)
    {
        // $data = json_decode($request->getContent(), true);

        // $identifier = $data['phoneOrEmail'];
        // $password = $data['password'];

        // // Determine if the user is logging in with an email or a phone number
        // $isPhoneNumber = preg_match('/^[0-9]{9}$/', $identifier);


        // if ($isPhoneNumber) {
        //     $user = $userRepository->findOneBy(['phone' => $identifier]);
        // } else {
        //     $user = $userRepository->findOneBy(['email' => $identifier]);
        // }

        // if (!$user) {
        //     throw new UserNotFoundException(sprintf('No user found for identifier "%s".', $identifier));
        // }

        // // Check the user's password
        // $isPasswordValid = $this->get('security.password_encoder')
        //     ->isPasswordValid($user, $password);

        // if (!$isPasswordValid) {
        //     throw new BadCredentialsException('Invalid password');
        // }

        // // Generate a JWT token for the authenticated user
        // $token = $this->jwtManager->create($user);

        // return new JsonResponse([
        //     'token' => $token,
        // ]);
    }
}
