<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\service\ResetPassworService;
use Doctrine\DBAL\Schema\View;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MyResetPassWordController extends AbstractController
{


    /**
     * @Route("/api/reset-password", name="api_reset_password_url" , methods={"POST"} )
     * @param Request $request
     */
    public function resetPassword(Request $request, ResetPassworService $resetPasswordService): Response
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $uri = $data['uri'];

        $result =  $resetPasswordService->processSendingPasswordResetEmail($email, $uri);

        return new JsonResponse($result, 200);
    }


    /**
     * @Route("/api/reset-password/new-password", name="api_reset_password_new" , methods={"POST"} )
     * @param Request $request
     */
    public function newPassword(Request $request, ResetPassworService $resetPasswordService): Response
    {

        $data = json_decode($request->getContent(), true);

        $password = $data['password'];
        $token = $data['token'];

        $resetPasswordService->newPassword($password, $token);

        return new JsonResponse("Mot de passe reinitialiser", 200);
    }
}
