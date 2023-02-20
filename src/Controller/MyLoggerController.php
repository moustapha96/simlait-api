<?php

namespace App\Controller;

use App\Entity\Profils;
use App\Repository\LoggerRepository;
use App\Repository\ProduitsRepository;

use App\Repository\ProfilsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MyLoggerController extends AbstractController
{


    public function __construct()
    {
    }
    /**
     * @Route("/api/loggers/deletes", name="app_loggers_deleltes",methods={"POST"})
     */
    public function deleteLogger(Request $request, LoggerRepository $repository, EntityManagerInterface $em): ?Response

    {
        try {

            $data = json_decode($request->getContent(), true);
            $id = $data['id'];
            $loge = $repository->findOneBy(array('id' => $id));

         

            if (!$em->isOpen()) {
                $em = $this->entityManager->create(
                    $em->getConnection(),
                    $em->getConfiguration()
                );
            }


            return new JsonResponse(['delete success'], 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }
}
