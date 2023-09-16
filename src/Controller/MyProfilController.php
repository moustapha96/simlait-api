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
                $quantite = $data['quantite'];
                $periode = $data['periode'];
                $indicatif = $data['indicatif'];


                $profil = new Profils();
                $profil->setNom($nom);
                $profil->setDenomination($denomination);
                $profil->setQuantite($quantite);
                $profil->setPeriode($periode);
                $profil->setIndicatif($indicatif);


                $profillast = $profilRepository->findOneBy([], ['id' => 'desc']);
                $idLast = $profillast->getId() + 1;
                $profil->setId($idLast);

                $entityManager->persist($profil);


                foreach ($produits as $pro) {
                    $p = $proRepo->find($pro["id"]);
                    $profillast->addProduit($p);
                }

                $entityManager->persist($profillast);

                if ($profil ==  null) {
                    throw  new  NotFoundHttpException(" profil non creer ");
                }

                $entityManager->flush();
            } else if ($request->getMethod() == 'PUT') {
                $data = json_decode($request->getContent(), true);
                $id = $data['id'];
                $nom = $data['nom'];
                $denomination = $data['denomination'];
                $quantite = $data['quantite'];
                $produits = $data['produits'];
                $periode = $data['periode'];
                $indicatif = $data['indicatif'];

                $profil = $profilRepository->find($id);
                $profil->setNom($nom);
                $profil->setDenomination($denomination);
                $profil->setQuantite($quantite);
                $profil->setPeriode($periode);
                $profil->setIndicatif($indicatif);

                foreach ($profil->getProduits() as $pro) {
                    $profil->removeProduit($pro);
                }

                foreach ($produits as $pro) {
                    $p = $proRepo->find($pro["id"]);
                    $profil->addProduit($p);
                }

                $entityManager->persist($profil);


                if ($profil ==  null) {
                    throw  new  NotFoundHttpException(" profil non mise a jour ");
                }
                $entityManager->flush();
            }
            return new JsonResponse($profil, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 400);
        }
    }
}
