<?php


namespace App\service;

use App\Entity\TableCounter;
use App\Repository\CollecteRepository;
use App\Repository\TableCounterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\String\UnicodeString;

/**
 * @Service()
 * @Tag(name="kernel.event_listener", event=KernelEvents::REQUEST)
 */

class StartupTask  implements EventSubscriberInterface
{

    public $em;
    public $repo;
    public      $mailer;
    public $repoCollecte;
    public function __construct(EntityManagerInterface $em, MailerInterface $mailer, CollecteRepository $repoCollecte, TableCounterRepository $repo)
    {
        $this->em = $em;
        $this->repo = $repo;
        $this->mailer = $mailer;
        $this->repoCollecte = $repoCollecte;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        $setting = [];
        if ($request->isMethod('POST') || $request->isMethod('PUT')) {
            $tables = [
                "simlait_zones", "simlait_user_mobiles", "simlait_users", "simlait_unites",
                "simlait_regions", "simlait_profils", "simlait_produits", "simlait_emballages",
                "simlait_departements", "simlait_collectes", "simlait_conditionnements"
            ];

            //prix min et prix max
            try {
                $sql_low_price = 'SELECT MIN(prix) as prix_min FROM simlait_collectes';
                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($sql_low_price);

                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative()[0]['prix_min'];

                $tableCounter = $this->repo->findOneBy(['name' => 'prix_min']);
                if ($tableCounter) {
                    $tableCounter->setValue($rc ? $rc : 0);
                } else {
                    $tableCounter = new TableCounter();
                    $tableCounter->setValue($rc ? $rc : 0);
                    $tableCounter->setName("prix_min");
                }
                $this->em->persist($tableCounter);
                $setting[] = array(
                    'name' => $tableCounter->getName(),
                    'value' =>  $tableCounter->getValue()
                );
            } catch (\Throwable $th) {
                dump($th);
            }
            //collecte certifier
            try {

                $no_certifier = count($this->repoCollecte->findBy(['isCertified' => 0]));
                $certified = count($this->repoCollecte->findBy(['isCertified' => 1]));

                $tableCounter = $this->repo->findOneBy(['name' => 'collecteCertified']);
                if ($tableCounter) {
                    $tableCounter->setValue($certified);
                } else {
                    $tableCounter = new TableCounter();
                    $tableCounter->setValue($certified);
                    $tableCounter->setName("collecteCertified");
                }

                $tableCounterNon = $this->repo->findOneBy(['name' => 'collecteNonCertified']);
                if ($tableCounterNon) {
                    $tableCounterNon->setValue($no_certifier);
                } else {
                    $tableCounterNon = new TableCounter();
                    $tableCounterNon->setValue($no_certifier);
                    $tableCounterNon->setName("collecteNonCertified");
                }
                $this->em->persist($tableCounter);
                $this->em->persist($tableCounterNon);
                $setting[] = array(
                    'name' => $tableCounter->getName(),
                    'value' =>  $tableCounter->getValue()
                );
            } catch (\Throwable $th) {
            }
            //le produit le plus collecter
            try {
                $sql_most_produit_collecte = "SELECT  p.nom AS produit,   COUNT(*) AS collecte_count FROM   simlait_collectes c   JOIN simlait_produits p ON c.produits_id = p.id GROUP BY   c.produits_id ORDER BY  collecte_count DESC LIMIT 1 ";

                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($sql_most_produit_collecte);

                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative()[0];

                $tableCounterName = $this->repo->findOneBy(['name' => "most_produit"]);
                if ($tableCounterName) {
                    $tableCounterName->setValue($rc['produit'] . " : " . $rc['collecte_count'] ? $rc['collecte_count'] : 0);
                } else {
                    $tableCounterName = new TableCounter();
                    $tableCounterName->setValue($rc['produit'] . " : " . $rc['collecte_count'] ? $rc['collecte_count'] : 0);
                    $tableCounterName->setName("most_produit");
                }

                $this->em->persist($tableCounterName);
                $setting[] = array(
                    'name' => $tableCounterName->getName(),
                    'value' =>  $tableCounterName->getValue()
                );
            } catch (\Throwable $th) {
            }

            //collecte par type de profil
            try {
                $sql_nbre_producteur = "SELECT  COUNT(*) AS collecte_count FROM simlait_collectes c 
                        JOIN simlait_unites u ON c.unites_id = u.id 
                        JOIN simlait_profils pr ON u.profil_id = pr.id 
                        WHERE pr.nom = 'PRODUCTEUR' ";

                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($sql_nbre_producteur);

                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative()[0]['collecte_count'];

                $tableCounter = $this->repo->findOneBy(['name' => 'PRODUCTEUR']);
                if ($tableCounter) {
                    $tableCounter->setValue($rc ? $rc : 0);
                } else {
                    $tableCounter = new TableCounter();
                    $tableCounter->setValue($rc ? $rc : 0);
                    $tableCounter->setName("PRODUCTEUR");
                }
                $this->em->persist($tableCounter);
                $setting[] = array(
                    'name' => $tableCounter->getName(),
                    'value' =>  $tableCounter->getValue()
                );
            } catch (\Throwable $th) {
                // dd($th);
            }

            try {
                $sql_nbre_collecteur = "SELECT  COUNT(*) AS collecte_count FROM simlait_collectes c 
                         JOIN simlait_unites u ON c.unites_id = u.id 
                        JOIN simlait_profils pr ON u.profil_id = pr.id 
                        WHERE pr.nom = 'COLLECTEUR' ";
                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($sql_nbre_collecteur);
                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative()[0]['collecte_count'];

                $tableCounter = $this->repo->findOneBy(['name' => 'COLLECTEUR']);
                if ($tableCounter) {
                    $tableCounter->setValue($rc ? $rc : 0);
                } else {
                    $tableCounter = new TableCounter();
                    $tableCounter->setValue($rc ? $rc : 0);
                    $tableCounter->setName("COLLECTEUR");
                }
                $this->em->persist($tableCounter);
                $setting[] = array(
                    'name' => $tableCounter->getName(),
                    'value' =>  $tableCounter->getValue()
                );
            } catch (\Throwable $th) {
            }

            try {
                $sql_nbre_transformateur = "SELECT  COUNT(*) AS collecte_count FROM simlait_collectes c 
                        JOIN simlait_unites u ON c.unites_id = u.id 
                        JOIN simlait_profils pr ON u.profil_id = pr.id 
                        WHERE pr.nom = 'TRANSFORMATEUR' ";
                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($sql_nbre_transformateur);
                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative()[0]['collecte_count'];

                $tableCounter = $this->repo->findOneBy(['name' => 'TRANSFORMATEUR']);
                if ($tableCounter) {
                    $tableCounter->setValue($rc ? $rc : 0);
                } else {
                    $tableCounter = new TableCounter();
                    $tableCounter->setValue($rc ? $rc : 0);
                    $tableCounter->setName("TRANSFORMATEUR");
                }
                $this->em->persist($tableCounter);
                $setting[] = array(
                    'name' => $tableCounter->getName(),
                    'value' =>  $tableCounter->getValue()
                );
            } catch (\Throwable $th) {
                // dd($th);
            }

            try {
                $sql_nbre_commercant = "SELECT  COUNT(*) AS collecte_count FROM simlait_collectes c 
                        JOIN simlait_unites u ON c.unites_id = u.id 
                        JOIN simlait_profils pr ON u.profil_id = pr.id 
                        WHERE pr.nom = 'COMMERCANT' ";
                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($sql_nbre_commercant);
                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative()[0]['collecte_count'];

                $tableCounter = $this->repo->findOneBy(['name' => 'COMMERCANT']);
                if ($tableCounter) {
                    $tableCounter->setValue($rc ? $rc : 0);
                } else {
                    $tableCounter = new TableCounter();

                    $tableCounter->setValue($rc ? $rc : 0);
                    $tableCounter->setName("COMMERCANT");
                }
                $this->em->persist($tableCounter);
                $setting[] = array(
                    'name' => $tableCounter->getName(),
                    'value' =>  $tableCounter->getValue()
                );
            } catch (\Throwable $th) {
            }

            try {
                $sql_nbre_eleveur = "SELECT  COUNT(*) AS collecte_count FROM simlait_collectes c 
                        JOIN simlait_unites u ON c.unites_id = u.id 
                        JOIN simlait_profils pr ON u.profil_id = pr.id 
                        WHERE pr.nom = 'ELEVEUR' ";
                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($sql_nbre_eleveur);
                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative()[0]['collecte_count'];

                $tableCounter = $this->repo->findOneBy(['name' => 'ELEVEUR']);
                if ($tableCounter) {
                    $tableCounter->setValue($rc);
                } else {
                    $tableCounter = new TableCounter();
                    $tableCounter->setValue($rc ? $rc : 0);
                    $tableCounter->setName("ELEVEUR");
                }
                $this->em->persist($tableCounter);
                $setting[] = array(
                    'name' => $tableCounter->getName(),
                    'value' =>  $tableCounter->getValue() || 0
                );
            } catch (\Throwable $th) {
            }

            foreach ($tables as $table) {
                $sql = 'SELECT COUNT(*) as count FROM ' . $table;
                try {
                    $connT = $this->em->getConnection();
                    $stmtT = $connT->prepare($sql);
                    $resultSetT = $stmtT->executeQuery();
                    $r = $resultSetT->fetchAllAssociative();
                    $count = $r[0]['count'];

                    $mot = new UnicodeString($table);
                    $name =  $mot->replace('simlait_', '');

                    $tableCounter = $this->repo->findOneBy(['name' => $name]);

                    if ($tableCounter) {
                        $tableCounter->setValue($count);
                    } else {
                        $tableCounter = new TableCounter();
                        $tableCounter->setValue($count);
                        $tableCounter->setName($name);
                    }
                    $setting[] = array(
                        'name' => $tableCounter->getName(),
                        'value' =>  $tableCounter->getValue()
                    );

                    $this->em->persist($tableCounter);
                } catch (Exception $e) {
                    $e =  sprintf('<error>Error occurred while counting rows in %s: %s</error>', $name, $e->getMessage());
                    dump($e);
                }
            }

            $this->em->flush();
        }
    }
}
