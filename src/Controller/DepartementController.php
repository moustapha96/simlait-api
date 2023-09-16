<?php

namespace App\Controller;

use App\Repository\DepartementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DepartementController extends AbstractController
{
    #[Route('/api/departementsSimple', name: 'app_departements_simple', methods: ['GET'])]

    public function findDepartementSimple(DepartementRepository $departementRepository): Response
    {
        $departements = $departementRepository->findAll();
        $resultats = [];
        foreach ($departements as $m) {
            $resultats[] = $m->asArraySimple();
        }
        return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
    }
}
