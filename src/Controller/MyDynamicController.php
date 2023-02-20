<?php


namespace App\Controller;

use App\Repository\DatasRepository;
use DateTime;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Symfony\Component\Routing\Annotation\Route;

class MyDynamicController extends AbstractController
{

    public $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    //fonction renvoyant la liste d'une table données
    /**
     * @Route("/api/get_datas/{classe}",name="app_dynamic_get", methods={"GET"})
     */
    public function getDynamicEntity(string $classe): Response
    {
        $entity = strtolower($classe);
        // if (!str_contains($entity, '_formulaires')) {
        //     $entity .=  "_formulaires";
        // }

        $sql =   "SELECT * FROM " . $entity;
        // $sql =   "SELECT * FROM zones";
        try {
            $conn = $this->em->getConnection();
            $stmt = $conn->prepare($sql);
            $resultSet = $stmt->executeQuery();
            $r = $resultSet->fetchAllAssociative();
            return new JsonResponse([$r]);
        } catch (Exception $e) {
            // throw new \RuntimeException(sprintf('Error while preparing the SQL: %s', $e->getMessage()));
            return new JsonResponse([]);
        }
    }


    /**
     * @Route("/api/search_datas",name="app_dynamic_search", methods={"POST"})
     */
    public function searchInEntuty(Request $request): Response
    {

        $data = json_decode($request->getContent(), true);
        $table = $data['table'];
        $data_table = $data['data'];
        $keys = array_keys($data_table);

        $vv = "";

        for ($i = 0; $i < count($keys); $i++) {
            if ($i <= count($keys) - 2) {
                $vv .= "" . $keys[$i] . " = '" . $data_table[$keys[$i]] . "' AND ";
            } else  $vv .= "" . $keys[$i] . " = " . $data_table[$keys[$i]];
        }

        $SQL_INSERT = "SELECT * FROM  " . $table . " where " .  $vv;

        try {
            $connT = $this->em->getConnection();
            $stmtT = $connT->prepare($SQL_INSERT);
            $resultSetT = $stmtT->executeQuery();
            $rT = $resultSetT->fetchAllAssociative();
            return new JsonResponse([$rT]);
        } catch (Exception $e) {
            return new JsonResponse([$e->getMessage()]);
        }
        return new JsonResponse(['pas de données ']);
    }

    /**
     * @Route("/api/delete_datas",name="app_dynamic_delete", methods={"POST"})
     */
    public function deleteData(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $table = $data['table'];
        $id = $data['id'];
        // if (!str_contains($table, '_formulaires')) {
        //     $table .=  "_formulaires";
        // }
        $sql = "DELETE FROM " . $table . " WHERE id = '" . $id . "'";
        try {
            $connT = $this->em->getConnection();
            $stmtT = $connT->prepare($sql);
            $resultSetT = $stmtT->executeQuery();
            $rT = $resultSetT->fetchAllAssociative();
            return new JsonResponse(['donnée supprimée']);
        } catch (Exception $e) {
            return new JsonResponse([$e->getMessage()]);
        }
    }


    /**
     * @Route("/api/save_datas",name="app_dynamic_post", methods={"POST"})
     */
    public function createDynamicEntity(Request $request): Response
    {

        $data =  json_decode($request->getContent(), true);
        $table = strtolower($data['table']);
        // if (!str_contains($table, '_formulaires')) {
        //     $table .=  "_formulaires";
        // }
        $data_table = $data['data'];
        $keys = array_keys($data_table);

        $temoin = $this->get_table_names($table);

        if ($temoin == 0) {
            $SQL_IF_EXISTE = "CREATE TABLE " . $table . " ( id int NOT NULL AUTO_INCREMENT PRIMARY KEY) ";
            try {
                $connT = $this->em->getConnection();
                $stmtT = $connT->prepare($SQL_IF_EXISTE);
                $resultSetT = $stmtT->executeQuery();
                $rT = $resultSetT->fetchAllAssociative();
            } catch (Exception $e) {
                echo "\nWarning example_Col already exists\n";
                echo $e->getMessage();
            }
        }
        $col_names = $this->get_column_names($table);
        $no_col = array_diff($keys, $col_names);



        foreach ($no_col as $k) {

            $size = strlen($data_table[$k]);

            $type = gettype($data_table[$k]);

            if ($type == "string"  && $this->checkIsAValidDate($data_table[$k]) == false && $size < 255) {
                $SQL_IF_EXISTE = " ALTER TABLE " . $table . " ADD " . $k . " VARCHAR(255) ";
            } else if ($type == "string" && $this->checkIsAValidDate($data_table[$k]) == true) {
                $SQL_IF_EXISTE = " ALTER TABLE " . $table . " ADD " . $k . " DATETIME ";
            } else if ($type == "string" && $size >= 255) {
                $SQL_IF_EXISTE = " ALTER TABLE " . $table . " ADD " . $k . " LONGTEXT ";
            } else if ($type == "BLOB") {
                $SQL_IF_EXISTE = " ALTER TABLE " . $table . " ADD " . $k . " BLOB ";
            } else {
                $SQL_IF_EXISTE = " ALTER TABLE " . $table . " ADD " . $k . " " . strtoupper($type);
            }

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


        $cc = "";
        $vv = "";

        for ($i = 0; $i < count($keys); $i++) {
            if ($i <= count($keys) - 2) {

                if ($this->checkIsAValidDate($data_table[$keys[$i]])  == true) {
                    $date = date('Y-m-d H:i:s', strtotime($data_table[$keys[$i]]));
                    $vv .= "'" . $date . "',";
                } else {
                    $vv .= "'" . $data_table[$keys[$i]] . "',";
                }
                $cc .= $keys[$i] . ",";
            } else {

                if ($this->checkIsAValidDate($data_table[$keys[$i]])  == true) {
                    $date = date('Y-m-d H:i:s', strtotime($data_table[$keys[$i]]));
                    $vv .= "'" . $date . "'";
                } else {
                    $vv .= "'" . $data_table[$keys[$i]] . "'";
                }
                $cc .=  $keys[$i];
            }
        }

        $SQL_INSERT = "INSERT INTO " . $table . " (" . $cc . ")  VALUES ("  . $vv . ")";

        try {
            $conn2 = $this->em->getConnection();
            $stmt2 = $conn2->prepare($SQL_INSERT);
            $resultSet2 = $stmt2->executeQuery();
            $r2 = $resultSet2->fetchAllAssociative();
            return new JsonResponse(["reussie"]);
        } catch (Exception $e) {
            return new JsonResponse([$e->getMessage()]);
        }

        return new JsonResponse([]);
    }

    ///fonction retournant les colonnes 
    public  function get_column_names($table)
    {
        $sql = 'DESCRIBE ' . $table;
        try {
            $conn = $this->em->getConnection();
            $stmt = $conn->prepare($sql);
            $resultSet = $stmt->executeQuery();
            $r = $resultSet->fetchAllAssociative();
        } catch (Exception $e) {
            throw new \RuntimeException(sprintf('Error while preparing the SQL: %s', $e->getMessage()));
        }
        $rows = array();
        foreach ($r as $rr) {
            $rows[] = $rr['Field'];
        }
        return $rows;
    }


    function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    function checkIsAValidDate($myDateString)
    {
        return (bool)strtotime($myDateString);
    }
    //fonction retournant 0 si la table n'exite pas , sinon autre chose 
    function get_table_names($table)
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
}
