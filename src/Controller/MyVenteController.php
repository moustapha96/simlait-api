<?php

namespace App\Controller;

use App\Entity\Collecte;
use App\Entity\Vente;
use App\Repository\UnitesRepository;
use App\Repository\VenteRepository;
use App\Repository\ConditionnementsRepository;
use App\Repository\EmballageRepository;
use App\Repository\ProduitsRepository;
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

class MyVenteController extends AbstractController
{

    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("/api/ventes/search", name="app_search_ventes",methods={"POST"})
     * @param Request $request
     */
    public function search(Request $request, VenteRepository $repo): ?Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $region = $data['region'];
            $department = $data['departement'];
            $produit = $data['produit'];
            $conditionnement = $data['conditionnement'];
            $unites = $data['laiterie'];
            $emballage = $data['emballage'];
            $dateDebut = $data['dateDebut'];
            $dateFin = $data['dateFin'];
            $zone = $data['zone'];
            $ventes = $repo->findParCriteria($region, $department, $zone, $produit, $conditionnement, $unites, $emballage, $dateDebut, $dateFin);
            $resultats = array();
            foreach ($ventes as $m) {
                $resultats[] = $m->asArray();
            }
            
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/api/ventes/certifiedAll", name="app_certified_ventes",methods={"GET"})
     * @param Request $request
     */
    public function enableCertificate(VenteRepository $repo, EntityManagerInterface $entityManagerInterface): ?Response
    {
        try {

            $ventes = $repo->findAll();

            foreach ($ventes as $m) {
                $m->setIsCertified(true);
                $entityManagerInterface->persist($m);
                $entityManagerInterface->flush();
            }

            $resultats = array();
            foreach ($ventes as $m) {
                $resultats[] = $m->asArray();
            }
            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }


    /**
     * @Route("/api/ventes/findLaiterieWithDemande", name="app_ventes_findLaiterieWithDemande",methods={"POST"})
     * @param Request $request
     * @return View
     */
    public function findLaiterieWithDemande(Request $request, VenteRepository $repo): ?Response
    {

        try {
            $data = json_decode($request->getContent(), true);
            $zone = $data['zone'];
            $besoin = $data['besoin'];
            $produit = $data['produit'];
            $dateDebut = $data['dateDebut'];
            $dateFin = $data['dateFin'];
            $ventes = $repo->findLaiterieWithDemande($zone, $besoin, $produit, $dateDebut, $dateFin);
            $resultats = array();
            foreach ($ventes as $m) {
                $coll = array(
                    'conditionnement' => $m['conditionnement'],
                    'produit' => $m['produit'],
                    'laiterie' => $m['laiterie'],
                    'region' => $m['region'],
                    'prix' => $m['prix'],
                    'zone' => $m['zone'],
                    'idLaiterie' => $m['idLaiterie'],
                    'departement' => $m['departement'],
                    'quantite' => $m['quantite'],
                    'emballage' => $m['emballage'],
                    'telephone' => $m['telephone'],
                    'adresse' => $m['adresse']
                );
                $resultats[] = $coll;
            }
            if (!$ventes) {
                throw new EntityNotFoundException("aucun resultat trouvé");
            }

            return new JsonResponse($resultats, 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse([], 200, ["Content-Type" => "application/json"]);
        }
    }

    /**
     * @Route("/api/ventes/create",name="app_ventes_create",methods={"POST"})
     */
    public function createCollecte(Request $request,  ProduitsRepository $pr, ConditionnementsRepository $cr, UnitesRepository $lr, UserMobileRepository $ur, EmballageRepository $er, EntityManagerInterface $em): Response
    {

        try {

            $data = json_decode($request->getContent(), true);
            $idProduit =  $data['idProduit'];
            $idConditionnement = $data['idConditionnement'];
            $idLaiterie = $data['idLaiterie'];
            $idUser = $data['idUser'];
            $idEmballage = $data['idEmballage'];
            $isSynchrone = $data['isSynchrone'];
            $isCertified = $data['isCertified'];
            $quantite = $data['quantite'];
            $prix = $data['prix'];
            $isDeleted = $data['isDeleted'];

            $produit = $pr->find($idProduit);
            $conditionnement = $cr->find($idConditionnement);
            $laiterie = $lr->find($idLaiterie);
            $user = $ur->find($idUser);
            $emballage = $er->find($idEmballage);
            $dateVente = $data['dateVente'];
            $date = new \DateTime($dateVente);
            $datei = \DateTimeImmutable::createFromMutable($date);
            $vente = new Vente();
            $vente->setConditionnements($conditionnement);
            $vente->setEmballages($emballage);
            $vente->setUser($user);
            $vente->setUnites($laiterie);
            $vente->setProduits($produit);
            $vente->setQuantite($quantite);
            $vente->setPrix($prix);
            $vente->setIsCertified($isCertified);
            $vente->setIsDeleted($isDeleted);
            $vente->setDateVente($datei);
            $vente->setIsSynchrone($isSynchrone);

            $em->persist($vente);
            $em->flush();

            return new JsonResponse([$vente->asArray()], 200, ["Content-Type" => "application/json"]);
       
        } catch (\Exception $e) {

            return new JsonResponse([$e->getMessage()], 500, ["Content-Type" => "application/json"]);
        }
       
    }
}
