<?php

namespace App\Controller;

use App\Entity\Collecte;
use App\Repository\CollecteRepository;
use App\Repository\ConditionnementsRepository;
use App\Repository\EmballageRepository;
use App\Repository\ProduitsRepository;
use App\Repository\UnitesRepository;
use App\Repository\UserMobileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Schema\View;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use EasyRdf\Serialiser\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MyCollecteController extends AbstractController
{


    /**
     * @Route("/api/collectes/search", name="app_search_collecte",methods={"POST"})
     * @param Request $request
     * @return View
     */
    public function search(Request $request, CollecteRepository $repo): ?Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $region = $data['region'];
            $department = $data['departement'];
            $produit = $data['produit'];
            $conditionnement = $data['conditionnement'];
            $unites = $data['unites'];
            $emballage = $data['emballage'];
            $dateDebut = $data['dateDebut'];
            $dateFin = $data['dateFin'];
            $zone = $data['zone'];
            $profil = $data['profil'];

            $collectes = $repo->findParCriteria($profil, $region, $department, $zone, $produit, $conditionnement, $unites, $emballage, $dateDebut, $dateFin);
            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }

            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/api/collectes/certifiedAll", name="app_certified_collecte",methods={"GET"})
     * @param Request $request
     * @return View
     */
    public function enableCertificate(CollecteRepository $repo, EntityManagerInterface $entityManagerInterface): ?Response
    {
        try {

            $collectes = $repo->findAll();
            foreach ($collectes as $m) {
                $m->setIsCertified(true);
                $entityManagerInterface->persist($m);
                $entityManagerInterface->flush();
            }

            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }


    /**
     * @Route("/api/collectes/findUnitesWithDemande", name="app_collecte_findUnitesWithDemande",methods={"POST"})
     * @param Request $request
     * @return View
     */
    public function findUnitesWithDemande(Request $request, CollecteRepository $repo): ?Response
    {

        try {
            $data = json_decode($request->getContent(), true);
            $zone = $data['zone'];
            $besoin = $data['besoin'];
            $produit = $data['produit'];
            $dateDebut = $data['dateDebut'];
            $dateFin = $data['dateFin'];
            $typeUnite = $data['typeUnite'];
            // dd($data);
            if ($typeUnite == 'unites') {
                $collectes = $repo->findUnitesWithDemandeUnites($zone, $besoin, $produit, $dateDebut, $dateFin);
            } else if ($typeUnite == "unitesAutre") {
                $collectes = $repo->findUnitesWithDemandeUnitesAutre('', $besoin, $produit, $dateDebut, $dateFin);
            }

            // $resultats = array();
            // foreach ($collectes as $m) {
            //     $coll = array(
            //         'conditionnement' => $m['conditionnement'],
            //         'produit' => $m['produit'],
            //         'unites' => $m['unites'],
            //         'region' => $m['region'],
            //         'prix' => $m['prix'],
            //         'zone' => $m['zone'],
            //         'idUnites' => $m['idUnites'],
            //         'departement' => $m['departement'],
            //         'quantite' => $m['quantite'],
            //         'emballage' => $m['emballage'],
            //         'telephone' => $m['telephone'],
            //         'adresse' => $m['adresse'],
            //         'unitesAutre'=> $m['idUntesAutres'],
            //         'unitesAutre' => $m['untesAutres']

            //     );
            //     $resultats[] = $coll;
            // }
            // if (!$collectes) {
            //     throw new EntityNotFoundException("aucun resultat trouvé");
            // }

            return new JsonResponse($collectes, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([], 200, ["Content-Type" => "application/json"]);
        }
    }

    /**
     * @Route("/api/collectes/create",name="app_collectes_create",methods={"POST"})
     */
    public function createCollecte(
        Request $request,
        ProduitsRepository $pr,
        ConditionnementsRepository $cr,
        UnitesRepository $lr,
        UserMobileRepository $ur,
        EmballageRepository $er,
        EntityManagerInterface $em
    ): Response {

        try {

            $data = json_decode($request->getContent(), true);
            $idProduit =  $data['idProduit'];
            $idConditionnement = $data['idConditionnement'];
            $idUnites = $data['idUnites'];
            $idUser = $data['idUser'];
            $idEmballage = $data['idEmballage'];
            $isSynchrone = $data['isSynchrone'];
            $isCertified = $data['isCertified'];
            $quantite = $data['quantite'];
            $prix = $data['prix'];
            $isDeleted = $data['isDeleted'];

            $quantite_vendu = $data['quantite_vendu'];
            $quantite_perdu = $data['quantite_perdu'];
            $quantite_autre = $data['quantite_autre'];

            $produit = $pr->find($idProduit);
            $conditionnement = $cr->find($idConditionnement);
            $unite = $lr->find($idUnites);
            $user = $ur->find($idUser);
            $emballage = $er->find($idEmballage);

            $dateSaisieinitial = $data['dateSaisie'];
            $dateSaisieFormater = new \DateTime($dateSaisieinitial);
            $dateSaisie = \DateTimeImmutable::createFromMutable($dateSaisieFormater);

            $dateCollecte = $data['dateCollecte'];
            $date = new \DateTime($dateCollecte);
            $datei = \DateTimeImmutable::createFromMutable($date);


            $collecte = new Collecte();
            $collecte->setConditionnements($conditionnement);
            $collecte->setEmballages($emballage);
            $collecte->setUser($user);
            $collecte->setUnites($unite);
            $collecte->setProduits($produit);
            $collecte->setQuantite($quantite);
            $collecte->setPrix($prix);
            $collecte->setIsCertified($isCertified);
            $collecte->setIsDeleted($isDeleted);
            $collecte->setDateCollecte($datei);
            $collecte->setIsSynchrone($isSynchrone);
            $collecte->setDateSaisie($dateSaisie);
            // if($unite->getProfil()->getNom() == "COLLECTEUR" ){
            //     $collecte->setQuantiteVendu(0);
            // }else {
            //     $collecte->setQuantitePerdu($quantite_perdu);
            //     $collecte->setQuantiteAutre($quantite_autre);
            //     $collecte->setQuantiteVendu($quantite_vendu);
            // }

            $collecte->setQuantitePerdu($quantite_perdu);
            $collecte->setQuantiteAutre($quantite_autre);
            $collecte->setQuantiteVendu($quantite_vendu);

            $em->persist($collecte);
            $em->flush();

            return new JsonResponse([$collecte->asArray()], 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {

            return new JsonResponse([$e->getMessage()], 500, ["Content-Type" => "application/json"]);
        }
    }


    /**
     * @Route("/api/eleveurs", name="app_collecte_eleveur" , methods={"GET"})
     */
    public function findCollecteEleveur(CollecteRepository $collecteRepository): ?Response
    {
        try {
            $collectes  = $collecteRepository->findCollecteEleveur();
            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 500, ["Content-Type" => "application/json"]);
        }
    }
    /**
     * @Route("/api/producteurs", name="app_collecte_producteur" , methods={"GET"})
     */
    public function findCollecteProducteur(CollecteRepository $collecteRepository): ?Response
    {
        try {
            $collectes  = $collecteRepository->findCollecteProducteur();
            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 500, ["Content-Type" => "application/json"]);
        }
    }
    /**
     * @Route("/api/collecteurs", name="app_collecte_collecteurs" , methods={"GET"})
     */
    public function findCollecteCollecteur(CollecteRepository $collecteRepository): ?Response
    {
        try {
            $collectes  = $collecteRepository->findCollecteCollecteur();
            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 500, ["Content-Type" => "application/json"]);
        }
    }

    /**
     * @Route("/api/commercants", name="app_collecte_commercants" , methods={"GET"})
     */
    public function findCollectecommercants(CollecteRepository $collecteRepository): ?Response
    {
        try {
            $collectes  = $collecteRepository->findCollecteCommercants();
            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 500, ["Content-Type" => "application/json"]);
        }
    }

    /**
     * @Route("/api/transformateurs", name="app_collecte_transformateurs" , methods={"GET"})
     */
    public function findCollectetransformateurs(CollecteRepository $collecteRepository): ?Response
    {
        try {
            $collectes  = $collecteRepository->findCollecteTransformateur();
            $resultats = array();
            foreach ($collectes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([$e->getMessage()], 500, ["Content-Type" => "application/json"]);
        }
    }
}
