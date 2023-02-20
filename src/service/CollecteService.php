<?php


namespace App\service;

use App\Repository\CollecteRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class CollecteService
{
    private $repo;

    public function __construct(CollecteRepository $repo)
    {
        $this->repo = $repo;
    }

    // /**
    //  * @param Request $request
    //  * @throws EntityNotFoundException
    //  * @return array|null
    //  */
    // public function searchCritere(Request $request): ?array
    // {

    //     $data = json_decode($request->getContent(), true);
    //     $region = $data['region'];
    //     $department = $data['departement'];
    //     $produit = $data['produit'];
    //     $conditionnement = $data['conditionnement'];
    //     $laiterie = $data['laiterie'];
    //     $emballage = $data['emballage'];
    //     $dateDebut = $data['dateDebut'];
    //     $dateFin = $data['dateFin'];
    //     $zone = $data['zone'];

    //     $collectes = $this->repo->findParCriteria($region, $department, $zone, $produit, $conditionnement, $laiterie, $emballage, $dateDebut, $dateFin);

    //     if (!$collectes) {
    //         throw new EntityNotFoundException("aucun collecte trouvé");
    //     }
      
    //     return $collectes;
    // }
}
