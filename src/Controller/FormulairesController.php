<?php

namespace App\Controller;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],

)]
class FormulairesController extends AbstractController
{

    private $client;
    public $em;
    public function __construct(HttpClientInterface $client, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->client = $client;
    }


    /**
     * @Route("/api/formulaires", name="app_formulaires", methods={"GET", "POST"})
     */

    public function formulaires(Request $request): Response
    {

        if ($request->getMethod() == 'POST') {
            $datas = json_decode($request->getContent(), true);
            $title = $datas['title'];
            // $title .= "_formulaires";

            $table = strtolower(str_replace(' ', '_', $datas['title']));
            // if (!str_contains($table, '_formulaires')) {
            //     $table .=  "_formulaires";
            // }

            $temoin = $this->get_table_names($table);

            if ($temoin == 0) {
                $SQL_IF_EXISTE = "CREATE TABLE " . $table . " ( id int NOT NULL AUTO_INCREMENT PRIMARY KEY) ";

                try {
                    $conn2 = $this->em->getConnection();
                    $stmt2 = $conn2->prepare($SQL_IF_EXISTE);
                    $resultSet2 = $stmt2->executeQuery();
                    $r2 = $resultSet2->fetchAllAssociative();
                } catch (Exception $e) {
                    echo "\nWarning example_Col already exists\n";
                    echo $e->getMessage();
                }
            }
        }


        try {
            $MYAGROPULSE_FORM_BASEURI = $this->getParameter('app.MYAGROPULSE_FORM_BASEURI');
            $response = null;
            if ($request->getMethod() == 'POST') {

                $response = $this->client->request($request->getMethod(), $MYAGROPULSE_FORM_BASEURI . '/formulaires', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept: application/json',

                    ], 'json' => json_decode($request->getContent(), true)
                ]);
            } elseif ($request->getMethod() == 'GET') {
                $response = $this->client->request(
                    $request->getMethod(),
                    $MYAGROPULSE_FORM_BASEURI . '/formulaires'
                );
            }

            $statusCode = $response->getStatusCode();
            $content = $response->toArray(FALSE);
            return new JsonResponse($content, $statusCode);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    //fonction retournant 0 si la table n'exite pas , sinon autre chose 
    public  function get_table_names($table)
    {
        $sql =   "SELECT count(*) FROM information_schema.TABLES WHERE (TABLE_SCHEMA = 'laiterie') AND (TABLE_NAME = '" . $table . "' )";

        try {
            $conn = $this->em->getConnection();
            $stmt = $conn->prepare($sql);
            $resultSet = $stmt->executeQuery();
            $r = $resultSet->fetchAllAssociative();
            return $r[0]['count(*)'];
        } catch (Exception $e) {
            throw new \RuntimeException(sprintf('Error while preparing the SQL: %s', $e->getMessage()));
        }
    }



    /**
     * @Route("/api/formulaires/title", name="app_formulaire_find_title",methods={"POST"})
     */
    public function oneByTitle(Request $request): Response
    {
        $MYAGROPULSE_FORM_BASEURI = $this->getParameter('app.MYAGROPULSE_FORM_BASEURI');

        try {
            $response = null;
            if ($request->getMethod() == 'POST') {

                $response = $this->client->request($request->getMethod(), $MYAGROPULSE_FORM_BASEURI . '/formulaires/title', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept: application/json',

                    ], 'json' => json_decode($request->getContent(), true)
                ]);
            }

            $statusCode = $response->getStatusCode();
            $content = $response->toArray(FALSE);
            return new JsonResponse($content, $statusCode);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }
    /**
     * @Route("/api/formulaires/{id}", name="app_formulaire",methods={"GET", "POST", "PUT", "DELETE"})
     */
    public function byOneformulaires(Request $request, String $id): Response
    {
        $MYAGROPULSE_FORM_BASEURI = $this->getParameter('app.MYAGROPULSE_FORM_BASEURI');
        $uri = "$MYAGROPULSE_FORM_BASEURI/formulaires/$id";
        try {

            $response = null;
            if ($request->getMethod() == 'PUT') {
                $response = $this->client->request($request->getMethod(), $uri, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept: application/json',
                    ], 'json' => json_decode($request->getContent(), true)
                ]);
            } elseif ($request->getMethod() == 'DELETE' || $request->getMethod() == 'GET') {
                $response = $this->client->request(
                    $request->getMethod(),
                    $uri
                );
            }

            $statusCode = $response->getStatusCode();
            $content = $response->toArray(FALSE);
            return new JsonResponse($content, $statusCode);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/api/data", name="app_data", methods={"GET", "POST"})
     */
    public function data(Request $request): Response
    {
        try {
            $MYAGROPULSE_FORM_BASEURI = $this->getParameter('app.MYAGROPULSE_FORM_BASEURI');
            $response = null;
            if ($request->getMethod() == 'POST') {

                $response = $this->client->request($request->getMethod(), $MYAGROPULSE_FORM_BASEURI . '/data', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept: application/json',

                    ], 'json' => json_decode($request->getContent(), true)
                ]);
            } elseif ($request->getMethod() == 'GET') {
                $response = $this->client->request(
                    $request->getMethod(),
                    $MYAGROPULSE_FORM_BASEURI . '/data'
                );
            }

            $statusCode = $response->getStatusCode();
            $content = $response->toArray(FALSE);
            return new JsonResponse($content, $statusCode);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 500);
        }
    }


    /**    
     *@Route("/api/allTable", name="api_all_table",methods={"GET"}  )
     */
    public function getAllTable(EntityManagerInterface $em): Response
    {

        $entities = $em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        $res = [];
        foreach ($entities as $en) {
            $res[] = $this->convertClassNameToShortcutNotations($en);
        }
        $resultats = [];
        foreach ($res as $r) {

            $lastChar = substr($r, -1);
            if ($lastChar != 's') $r .= 's';
            $resultats[] = strtolower($r);
        }

        return  new JsonResponse($res);
    }

    public function convertClassNameToShortcutNotations($className)
    {
        $cleanClassName = str_replace('App\\Entity\\', '', $className);
        $parts = explode('\\', $cleanClassName);

        return implode('', $parts);
    }

    /**    
     *@Route("/api/getData", name="api_get_data_table",methods={"POST"}  )
     */
    public function getData(Request $request, ManagerRegistry $doctrine, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
        $t =  $data['name'];
        $s =   "App\\Entity\\{$t}";
        $res = $doctrine->getRepository($s)->findAll();
        $resultat = [];
        foreach ($res as $r) {
            $resultat[] = $r->asArray();
        }
        return new JsonResponse($resultat);
    }
}