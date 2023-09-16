<?php

namespace App\Controller;

use App\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegionController extends AbstractController
{
    #[Route('/api/regionsSimple', name: 'app_region_simple', methods: ['GET'])]

    public function findDepartementSimple(RegionRepository $reiongRepository): Response
    {
        $departements = $reiongRepository->findAll();
        $resultats = [];
        foreach ($departements as $m) {
            $resultats[] = $m->asArray();
        }
        return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
    }
}
