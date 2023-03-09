<?php


namespace App\service;

use App\Entity\TableCounter;
use App\Repository\TableCounterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\MailerInterface;

/**
 * @Service()
 * @Tag(name="kernel.event_listener", event=KernelEvents::REQUEST)
 */

class StartupTask  implements EventSubscriberInterface
{

    public $em;
    public $repo;
    public      $mailer;
    public function __construct(EntityManagerInterface $em, MailerInterface $mailer, TableCounterRepository $repo)
    {
        $this->em = $em;
        $this->repo = $repo;
        $this->mailer = $mailer;
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
        if ($request->isMethod('POST')) {
            $tables = [
                "zones", "user_mobile", "user", "unites",
                "region", "profils", "produits", "emballage",
                "departement", "collecte", "conditionnements"
            ];
            try {
                $sql_low_price = 'SELECT MIN(prix) as prix_min FROM collecte';
                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($sql_low_price);

                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative()[0]['prix_min'];

                $tableCounter = $this->repo->findOneBy(['name' => 'prix_min']);
                if ($tableCounter) {
                    $tableCounter->setValue($rc);
                } else {
                    $tableCounter = new TableCounter();
                    $tableCounter->setValue($rc);
                    $tableCounter->setName("prix_min");
                }
                $this->em->persist($tableCounter);
                $setting[] = array(
                    'name' => $tableCounter->getName(),
                    'value' =>  $tableCounter->getValue()
                );
            } catch (\Throwable $th) {
            }

            try {
                $sql_certified = "SELECT SUM(CASE WHEN is_certified = 1 THEN 1 ELSE 0 END) AS collecteCertified,  SUM(CASE WHEN is_certified = 0 THEN 1 ELSE 0 END) AS collecteNonCertified FROM  collecte";
                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($sql_certified);
                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative()[0];
                $tableCounter = $this->repo->findOneBy(['name' => 'collecteCertified']);

                if ($tableCounter) {
                    $tableCounter->setValue($rc['collecteCertified']);
                } else {
                    $tableCounter = new TableCounter();
                    $tableCounter->setValue($rc['collecteCertified']);
                    $tableCounter->setName("collecteCertified");
                }

                $tableCounterNon = $this->repo->findOneBy(['name' => 'collecteNonCertified']);
                if ($tableCounterNon) {
                    $tableCounterNon->setValue($rc['collecteNonCertified']);
                } else {
                    $tableCounterNon = new TableCounter();
                    $tableCounterNon->setValue($rc['collecteNonCertified']);
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

            try {
                $sql_most_produit_collecte = "SELECT  p.nom AS produit,   COUNT(*) AS collecte_count FROM   collecte c   JOIN produits p ON c.produits_id = p.id GROUP BY   c.produits_id ORDER BY  collecte_count DESC LIMIT 1 ";

                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($sql_most_produit_collecte);

                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative()[0];

                $tableCounterName = $this->repo->findOneBy(['name' => "most_produit"]);
                // $tableCounterValue = $this->repo->findOneBy(['name' => "most_produit_value"]);
                if ($tableCounterName) {
                    $tableCounterName->setValue($rc['produit'] . " : " . $rc['collecte_count']);
                } else {
                    $tableCounterName = new TableCounter();
                    $tableCounterName->setValue($rc['produit'] . " : " . $rc['collecte_count']);
                    $tableCounterName->setName("most_produit");
                }

                $this->em->persist($tableCounterName);
                $setting[] = array(
                    'name' => $tableCounterName->getName(),
                    'value' =>  $tableCounterName->getValue()
                );
            } catch (\Throwable $th) {
            }

            try {
                $sql_nbre_producteur = "SELECT  COUNT(*) AS collecte_count FROM collecte c 
                        JOIN produits p ON c.produits_id = p.id 
                        JOIN produits_profils pp ON p.id = pp.produits_id  
                        JOIN profils pr ON pp.profils_id = pr.id 
                        WHERE pr.nom = 'PRODUCTEUR' ";
                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($sql_nbre_producteur);

                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative()[0]['collecte_count'];

                $tableCounter = $this->repo->findOneBy(['name' => 'PRODUCTEUR']);
                if ($tableCounter) {
                    $tableCounter->setValue($rc);
                } else {
                    $tableCounter = new TableCounter();
                    $tableCounter->setValue($rc);
                    $tableCounter->setName("PRODUCTEUR");
                }
                $this->em->persist($tableCounter);
                $setting[] = array(
                    'name' => $tableCounter->getName(),
                    'value' =>  $tableCounter->getValue()
                );
            } catch (\Throwable $th) {
            }

            try {
                $sql_nbre_collecteur = "SELECT  COUNT(*) AS collecte_count FROM collecte c 
                        JOIN produits p ON c.produits_id = p.id 
                        JOIN produits_profils pp ON p.id = pp.produits_id  
                        JOIN profils pr ON pp.profils_id = pr.id 
                        WHERE pr.nom = 'COLLECTEUR' ";
                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($sql_nbre_collecteur);
                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative()[0]['collecte_count'];

                $tableCounter = $this->repo->findOneBy(['name' => 'COLLECTEUR']);
                if ($tableCounter) {
                    $tableCounter->setValue($rc);
                } else {
                    $tableCounter = new TableCounter();
                    $tableCounter->setValue($rc);
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
                $sql_nbre_transformateur = "SELECT  COUNT(*) AS collecte_count FROM collecte c 
                        JOIN produits p ON c.produits_id = p.id 
                        JOIN produits_profils pp ON p.id = pp.produits_id  
                        JOIN profils pr ON pp.profils_id = pr.id 
                        WHERE pr.nom = 'TRANSFORMATEUR' ";
                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($sql_nbre_transformateur);
                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative()[0]['collecte_count'];

                $tableCounter = $this->repo->findOneBy(['name' => 'TRANSFORMATEUR']);
                if ($tableCounter) {
                    $tableCounter->setValue($rc);
                } else {
                    $tableCounter = new TableCounter();
                    $tableCounter->setValue($rc);
                    $tableCounter->setName("TRANSFORMATEUR");
                }
                $this->em->persist($tableCounter);
                $setting[] = array(
                    'name' => $tableCounter->getName(),
                    'value' =>  $tableCounter->getValue()
                );
            } catch (\Throwable $th) {
            }

            try {
                $sql_nbre_commercant = "SELECT  COUNT(*) AS collecte_count FROM collecte c 
                        JOIN produits p ON c.produits_id = p.id 
                        JOIN produits_profils pp ON p.id = pp.produits_id  
                        JOIN profils pr ON pp.profils_id = pr.id 
                        WHERE pr.nom = 'COMMERCANT' ";
                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($sql_nbre_commercant);
                $resultSetT = $stmtT->executeQuery();
                $rc = $resultSetT->fetchAllAssociative()[0]['collecte_count'];

                $tableCounter = $this->repo->findOneBy(['name' => 'COMMERCANT']);
                if ($tableCounter) {
                    $tableCounter->setValue($rc);
                } else {
                    $tableCounter = new TableCounter();
                    $tableCounter->setValue($rc);
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
                $sql_nbre_eleveur = "SELECT  COUNT(*) AS collecte_count FROM collecte c 
                        JOIN produits p ON c.produits_id = p.id 
                        JOIN produits_profils pp ON p.id = pp.produits_id  
                        JOIN profils pr ON pp.profils_id = pr.id 
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
                    $tableCounter->setValue($rc);
                    $tableCounter->setName("ELEVEUR");
                }
                $this->em->persist($tableCounter);
                $setting[] = array(
                    'name' => $tableCounter->getName(),
                    'value' =>  $tableCounter->getValue()
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

                    $tableCounter = $this->repo->findOneBy(['name' => $table]);

                    if ($tableCounter) {
                        $tableCounter->setValue($count);
                    } else {
                        $tableCounter = new TableCounter();
                        $tableCounter->setValue($count);
                        $tableCounter->setName($table);
                    }
                    $setting[] = array(
                        'name' => $tableCounter->getName(),
                        'value' =>  $tableCounter->getValue()
                    );

                    $this->em->persist($tableCounter);
                } catch (Exception $e) {
                    $e =  sprintf('<error>Error occurred while counting rows in %s: %s</error>', $table, $e->getMessage());
                    dump($e);
                }
            }
            $file = 'config/stats-db.json';
            $dir = dirname($file);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            file_put_contents($file, json_encode($setting));
            $this->em->flush();
        }
    }
}