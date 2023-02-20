<?php

namespace App\Controller;

use App\Entity\Profils;
use App\Repository\ProduitsRepository;

use App\Repository\ProfilsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MyProfilController extends AbstractController
{


    public function __construct()
    {
    }
    /**
     * @Route("/api/createProfil", name="app_store_profil",methods={"POST","PUT"})
     */
    public function storeProfil(Request $request, ProfilsRepository $profilRepository, EntityManagerInterface $entityManager, ProduitsRepository $proRepo): ?Response

    {
        try {
            if ($request->getMethod() == 'POST') {
                $data = json_decode($request->getContent(), true);

               
                
                $nom = $data['nom'];
                $produits = $data['produits'];
                $denomination = $data['denomination'];
                $profil = new Profils();
                $profil->setNom($nom);
                $profil->setDenomination($denomination);
                $entityManager->persist($profil);
                $entityManager->flush();
                $profillast = $profilRepository->findOneBy([], ['id' => 'desc']);
                foreach ($produits as $pro) {
                    $p = $proRepo->find($pro["id"]);
                    $profillast->addProduit($p);
                }
                $entityManager->persist($profillast);
                $entityManager->flush();
                if ($profil ==  null) {
                    throw  new  NotFoundHttpException(" profil non creer ");
                }
            } else if ($request->getMethod() == 'PUT') {
                $data = json_decode($request->getContent(), true);
                $id = $data['id'];
                $nom = $data['nom'];
                $denomination = $data['denomination'];

                $produits = $data['produits'];

                $profil = $profilRepository->find($id);
                $profil->setNom($nom);
                $profil->setDenomination($denomination);

                foreach ($profil->getProduits() as $pro){
                    $profil->removeProduit($pro);
                }

                foreach ($produits as $pro) {
                    $p = $proRepo->find($pro["id"]);
                    $profil->addProduit($p);
                }
                $entityManager->persist($profil);
                $entityManager->flush();

                if ($profil ==  null) {
                    throw  new  NotFoundHttpException(" profil non mise a jour ");
                }
            }
            return new JsonResponse([], 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

}
