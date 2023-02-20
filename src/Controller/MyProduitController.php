<?php

namespace App\Controller;

use App\Entity\Produits;
use App\EventListener\Produit\ProduitCreateEvent;
use App\EventListener\ProduitEventSubscriber;
use App\Repository\ConditionnementsRepository;
use App\Repository\ProduitsRepository;
use App\Repository\UnitesRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MyProduitController extends AbstractController
{

    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/api/storeProduit", name="app_store_produit",methods={"POST","PUT"})
     */
    public function storeProduit(
        Request $request,
        ProduitsRepository $repo,
        EntityManagerInterface $entityManager,
        ConditionnementsRepository $condiRepo
    ): ?Response {

        try {
            if ($request->getMethod() == 'POST') {
                $data = json_decode($request->getContent(), true);
                $nom = $data['nom'];
                $description = $data['description'];
                $conditionnements = $data['conditionnements'];
                $statut = $data['statut'];
                $unite = $data['unite'];
                $produit = new Produits();
                $produit->setNom($nom);
                $produit->setDescription($description);
                $produit->setStatut($statut);
                $produit->setUnite($unite);
                $entityManager->persist($produit);
                $entityManager->flush();
                $produitlast = $repo->findOneBy([], ['id' => 'desc']);

                foreach ($conditionnements as $condi) {

                    $co = $condiRepo->find($condi["id"]);
                    $produitlast->addConditionnement($co);
                }

                $entityManager->persist($produitlast);
                $entityManager->flush();

                if ($produit ==  null) {
                    throw  new  NotFoundHttpException(" produit non creer ");
                }
            } else if ($request->getMethod() == 'PUT') {
                $data = json_decode($request->getContent(), true);
                $id = $data['id'];
                $nom = $data['nom'];
                $unite = $data['unite'];
                $description = $data['description'];
                $conditionnements = $data['conditionnements'];
                $statut = $data['statut'];
                $produit = $repo->find($id);
                $produit->setNom($nom);
                $produit->setDescription($description);
                $produit->setStatut($statut);
                $produit->setUnite($unite);
                foreach ($conditionnements as $condi) {
                    $co = $condiRepo->find($condi["id"]);
                    $produit->addConditionnement($co);
                }
                $entityManager->persist($produit);
                $entityManager->flush();

                if ($produit ==  null) {
                    throw  new  NotFoundHttpException(" produit non mise a jour ");
                }
            }
            return new JsonResponse([], 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }


    // profuits utiliser par le producteur
    /**
     * @Route("/api/produitProducteurs", name="app_produits_producteur", methods={"GET"} )
     */
    public function getProduitProducteurs(ProduitsRepository $produitsRepository): Response
    {
        $produits = $produitsRepository->findProductions();
        $resultats = [];
        foreach ($produits as $value) {
            $resultats[] = $value->asArrayCondi();
        }
        return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
    }
    // profuits utiliser par le transformateur
    /**
     * @Route("/api/produitTransformateurs", name="app_produits_transformateur", methods={"GET"} )
     */
    public function getProduitTransformateur(ProduitsRepository $produitsRepository, ConditionnementsRepository $condiPro): Response
    {

        $produits = $produitsRepository->findTransformateur();

        $resultats = [];
        foreach ($produits as $value) {

            $resultats[] = $value->asArrayCondi();
        }


        return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
    }
    /**
     * @Route("/api/produitCollecteurs", name="app_produits_collecteur", methods={"GET"} )
     */
    public function getProduitCollecteur(ProduitsRepository $produitsRepository): array
    {

        $produits = $produitsRepository->findCollecteur();
        $resultats = [];

        foreach ($produits as $value) {
            dump($value->getConditionnements());
            $resultats[] = $value->asArrayCondi();
        }

        return  $resultats;

        // return new JsonResponse($resultats,200,["Content-Type" => "application/json"]);
    }

    /**
     * @Route("/api/produits/byProfil",name="app_produits_profil",methods={"POST"})
     */
    public function getProduitsProfil(Request $request, ProduitsRepository $produitsRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $produits = $produitsRepository->getProduitbyProfil($data['idProfil']);

        $resultats = [];
        foreach ($produits as $p) {
            $condi = array();
            foreach ($p->getConditionnements() as $c) {
                $condi[] = $c->asArray();
            }
            $pc = [
                'id' => $p->getId(),
                'nom' => $p->getNom(),
                'description' => $p->getDescription(),
                'statut' => $p->getStatut(),
                'conditionnements' => $condi,
                'unite' => $p->getUnite()
            ];
            $resultats[] = $pc;
        }

        return new JsonResponse($resultats);
    }
    // /**
    //  * @Route("/api/unites/byProfil",name="app_unites_profil",methods={"POST"})
    //  */
    // public function getUniteByProfil(Request $request, UnitesRepository $unitesRepository): Response
    // {

    //     $data = json_decode($request->getContent(), true);
    //     $unites = $unitesRepository->getUnitebyProfil($data['idProfil']);

    //     dd($unites);
    // }
}