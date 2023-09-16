<?php

namespace App\Controller;

use App\Entity\UnitesAutre;
use App\Entity\UnitesDemande;
use App\Entity\UserMobile;
use App\Repository\DepartementRepository;
use App\Repository\ModelSmsRepository;
use App\Repository\ProduitsRepository;
use App\Repository\ProfilsRepository;
use App\Repository\RegionRepository;
use App\Repository\StatusRepository;
use App\Repository\UnitesAutreRepository;
use App\Repository\UnitesDemandeRepository;
use App\Repository\UserMobileRepository;
use App\service\ConfigurationService;
use App\service\OrangeSMSService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Uid\Uuid;

class MyUnitesAutreController extends AbstractController
{

    /**
     * @param EntityManagerInterface $entityManager
     */
    private $entityManager;
    private $passwordEncoder;
    private $config;

    public function __construct(ConfigurationService $config, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorageInterface, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager)
    {
        $this->config = $config;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/unites_autres/demande/create",name="create_user_autres_demande", methods={"POST"})
     */
    public function create(MailerInterface $mailer, ProduitsRepository $pr, Request $request, UnitesAutreRepository $repo): ?Response
    {
        $data  = json_decode($request->getContent(), true);
        $user = new UnitesAutre();
        $user->setPrenom($data['prenom']);
        $user->setNom($data['nom']);
        $user->setTelephone($data['telephone']);
        $user->setAdresse($data['adresse']);
        $user->setEmail($data['email']);

        $dateSaisieinitial = $data['createAt'];
        $dateSaisieFormater = new \DateTime($dateSaisieinitial);
        $date = \DateTimeImmutable::createFromMutable($dateSaisieFormater);
        $user->setCreatedAt($date);

        $users = $repo->findBy(['email' => $user->getEmail()]);
        if ($users) {
            return new JsonResponse(["adresse email existe deja "], 400);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $results = $repo->findBy(array(), array('id' => 'DESC'), 1, 0)[0];

        if ($results->getId()) {
            $demande = new UnitesDemande();
            $demande->setBesoin($data['besoin']);

            $dfd = new \DateTime($data['dateDebut']);
            $dateDebut = \DateTimeImmutable::createFromMutable($dfd);

            $dff = new \DateTime($data['dateFin']);
            $dateFin = \DateTimeImmutable::createFromMutable($dff);

            $demande->setDateDebut($dateDebut);
            $demande->setDateFin($dateFin);


            $produit = $pr->find($data['produits']['id']);
            $demande->setProduits($produit);
            $demande->setUnitesAutre($results);
            $demande->setUnites(null);
            $demande->setStatut("NON DISPONIBLE");

            $this->entityManager->persist($demande);
            $this->entityManager->flush();

            return new JsonResponse(['demande creer'], 200);
        } else {
            return new JsonResponse([$user], 200);
        }
    }

    /**
     * @Route("api/unites_autresSimple",name="app_unite_autre_simple", methods={"GET"})
     */
    public function getSimpleUniteAutre(UnitesAutreRepository $unitesAutreRepository): Response
    {
        $unites = $unitesAutreRepository->findAll();
        $resultats = [];

        foreach ($unites as $key => $value) {
            $resultats[] = $value->asArray();
        }

        return new JsonResponse($resultats, 200);
    }


    /**
     * @Route("/unites_autres/demande/update",name="update_user_autres_demande", methods={"POST"})
     */
    public function update(ProduitsRepository $pr, UnitesDemandeRepository $undrepo,  Request $request, UnitesAutreRepository $repo): ?Response
    {

        $data  = json_decode($request->getContent(), true);
        $ud = $undrepo->find($data['id']);

        $user = $ud->getUnitesAutre();

        $user->setPrenom($data['prenom']);
        $user->setNom($data['nom']);
        $user->setTelephone($data['telephone']);
        $user->setAdresse($data['adresse']);
        $user->setEmail($data['email']);


        $users = $repo->findBy(['email' => $user->getEmail(), 'id' => $user->getId()])[0];
        if ($users != $user) {
            return new JsonResponse(["adresse email existe deja "], 400);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();


        $ud->setBesoin($data['besoin']);
        $ud->setStatut($data['statut']);
        // $dateDebut = $data['dateDebut'];
        // $dateFin = $data['dateFin'];
        // $demande->setDateDebut(  new \DateTime('@'.strtotime('now')) );
        // $demande->setDateFin(  new \DateTime('@'.strtotime('now'))  );


        $dfd = new \DateTime($data['dateDebut']);
        $dateDebut = \DateTimeImmutable::createFromMutable($dfd);

        $dff = new \DateTime($data['dateFin']);
        $dateFin = \DateTimeImmutable::createFromMutable($dff);

        $ud->setDateDebut($dateDebut);
        $ud->setDateFin($dateFin);


        $produit = $pr->find($data['produits']['id']);
        $ud->setProduits($produit);

        $ud->setUnites(null);
        $this->entityManager->persist($ud);
        $this->entityManager->flush();

        return new JsonResponse(['demande modifier avec succés'], 200);
    }




    /**
     * @Route("api/unites_autres/createCompte",name="unite_autres_create_compte", methods={"POST"})
     */
    public function createCompte(
        Request $request,
        UserMobileRepository $userMobileRepository,
        OrangeSMSService  $orangeSMSService,
        StatusRepository $str,
        RegionRepository $rr,
        ProfilsRepository $pr,
        DepartementRepository $dr,
        ModelSmsRepository $modelSmsRepository
    ): Response {

        $data  = json_decode($request->getContent(), true);
        $user = new UserMobile();

        $profil = $pr->findOneBy(['id' => $data['idProfil']]);
        $departement =  $dr->findOneBy(['id' => $data['idDepartement']]);
        $region = $rr->findOneBy(['id' => $data['idRegion']]);
        $status = $str->findOneBy(['id' => $data['idStatus']]);

        $user->setPrenom($data['prenom']);
        $user->setNom($data['nom']);
        $user->setTelephone($data['telephone']);
        $user->setAdresse($data['adresse']);
        $user->setEmail($data['email']);
        $user->setSexe($data['sexe']);
        $user->setEnabled(true);
        $user->setSexe($data['sexe']);

        $user->setUuid(Uuid::v4()->toRfc4122());
        $user->setLocalite($data['localite']);
        $user->setRegion($region);
        $user->setStatus($status);
        $user->setDepartement($departement);
        $user->setProfil($profil);
        $user->setHasLaiteries(false);
        $user->setPassword('password');

        $users = $userMobileRepository->findBy(['email' => $user->getEmail()]);
        if ($users) {
            return new JsonResponse("adresse email existe deja", 400);
        }

        $users = $userMobileRepository->findBy(['telephone' => $user->getTelephone()]);
        if ($users) {
            return new JsonResponse("Numéro Téléphone deja utilisé", 400);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $sms = $modelSmsRepository->findOneBy(['code' => 'CPTE_CREER']);
        if ($sms) {

            $message = $sms->getMessage();
            if ($sms->getParametre() != null && count($sms->getParametre()) != 0) {

                $parametres = $sms->getParametre();
                foreach ($parametres as $value) {
                    if ($value != '' && array_key_exists($value, $data)) {
                        $message = str_replace("[" . $value . "]", $data[$value], $message);
                    }
                }
            }
        }

        $response = $orangeSMSService->sendSMS($data['telephone'], $message);

        return new JsonResponse($user->asArray(), 200);
    }


    /**
     * @Route("api/unites_autres/compteExiste/{id}",name="unite_autres_compte_existe", methods={"GET"})
     */
    public function compteExiste(
        int $id,
        UnitesAutreRepository $unitesAutreRepository,
        UserMobileRepository $userMobileRepository,
    ): Response {
        $unite = $unitesAutreRepository->find($id);
        if($unite ){
          $critere = [
              'telephone' => $unite->getTelephone(),
              //  'email' => $unite->getEmail(),
              'prenom' => $unite->getPrenom(),
              'nom' => $unite->getNom(),
              'adresse' => $unite->getAdresse()
          ];
          $user = $userMobileRepository->findOneBy($critere);
          if ($user) {
              return new JsonResponse(true, 200);
          } else {
              return new JsonResponse(false, 200);
          }

        }

        return new JsonResponse(false, 200);
    }


    /**
     * @Route("api/unites_autres/delete/{id}",name="unite_autres_compte_delete", methods={"GET"})
     */
    public function deleteDemande(
        int $id,
        UnitesDemandeRepository $unitesDemandeRepository,
        UnitesAutreRepository $unitesAutreRepository,
    ): Response {

        $unite = $unitesAutreRepository->find($id);
        $demandes = $unitesDemandeRepository->findOneBy(["unitesAutre" => $unite]);

        if ($demandes) {
            $unitesDemandeRepository->remove($demandes);
        }

        return new JsonResponse(true, 200);
    }
}
