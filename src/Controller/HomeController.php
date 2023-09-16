<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index()
    {
        return new JsonResponse('Api SERVEUR');
        // return $this->renderView('home.home.html/twig');
    }


    //fonction verification accessibilité api
    /**
     * @Route("/api/test", name="app_test_api",methods={"GET"})
     */
    public function testUrlAPI(): Response
    {
        return new JsonResponse(["https://apisimlait.mascosolutions.com/api", "https://apidemosimlait.mascosolutions.com/api"], 200);
    }

    //fonction verification accessibilité api
    /**
     * @Route("/api/help", name="app_help",methods={"GET"})
     */
    public function docHelp(): Response
    {
        $manule_mobile = "https://apisimlait.mascosolutions.com/docs/MANUEL_MOBILE_SIMLAIT_PDEPS.pdf";
        $manule_WEB = "https://apisimlait.mascosolutions.com/docs/MANUEL_WEB_SIMLAIT_PDEPS.pdf";

        return new JsonResponse($manule_mobile, 200);
    }
}
