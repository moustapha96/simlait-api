<?php

namespace App\Controller;

use App\Entity\UnitesAutre;
use App\Entity\UnitesDemande;
use App\Entity\User;
use App\Entity\UserAutre;
use App\Repository\LoggerRepository;
use App\Repository\ProduitsRepository;
use App\Repository\UnitesAutreRepository;
use App\Repository\UnitesDemandeRepository;
use App\Repository\UnitesRepository;
use App\Repository\UserAutreRepository;
use App\Repository\UserMobileRepository;
use App\Repository\UserRepository;
use App\service\ConfigurationService;
use Container6HtSaCr\getUnitesAutreRepositoryService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Mailer\MailerInterface;


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
            return new JsonResponse(["adresse email existe deja "], 500);
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
            return new JsonResponse(["adresse email existe deja "], 500);
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
}
