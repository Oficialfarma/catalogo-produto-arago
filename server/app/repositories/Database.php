<?php
namespace App\Repositories;

use App\Interfaces\IDatabaseRepository;
use App\Utils\ErrorMessages;
use App\Utils\RegisterLog;
use Error;
use PDO;
use PDOException;

class Database implements IDatabaseRepository
{
    public $PDO;
    private $tablename;
    
    public function __construct()
    {
        $host = $_ENV['HOST'];
        $database = $_ENV['DATABASE'];
        $username = $_ENV['USERNAME'];
        $password = $_ENV['PASSWORD'];
        $this->tablename = $_ENV['TABLENAME'];
        
        try
        {
            $this->PDO = new PDO(
                "mysql:host={$host};dbname={$database}",
                $username,
                $password,
                array(PDO::ATTR_PERSISTENT => true)
            );
        }
        catch(PDOException $err)
        {
            RegisterLog::RegisterLog("Database Exception", $err->getMessage(), "exceptions.log");
            ErrorMessages::returnMessageError(500, "Internal Server Error", $err, "Erro conectando ao banco de dados");
        }
    }

    /**
     * Obtém a instância de acesso ao banco
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->PDO;
    }

    /**
     * Encontra todos os produtos limitando a busca com os valores passados
     *
     * @param int $actualPageLimitInit
     * @param int $limit
     * @return array|false
     */
    public function findAllProducts($actualPageLimitInit, $limit): array|false
    {
        $stmt = $this->PDO->prepare("SELECT * FROM {$this->tablename} LIMIT {$actualPageLimitInit},{$limit}");
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * Filtra os produtos com base na query string passada
     *
     * @param array $filter
     * @param int   $actualPageLimitInit
     * @param int   $limit
     * @return array|false
     */
    public function filterProducts($filter, $actualPageLimitInit, $limit): array|false
    {
        if(empty($filter))
        {
            return $this->findAllProducts($actualPageLimitInit, $limit);
        }
        
        $whereFilters = $this->performWhereFilters($filter);

        $stmt = $this->PDO->prepare("SELECT * FROM {$this->tablename} WHERE {$whereFilters} LIMIT {$actualPageLimitInit},{$limit}");
        $stmt->execute();
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * Verifica se o produto já existe na base e se não existir, insere
     *
     * @param int $productID
     * @param array $columnsAndValues
     * @return mixed
     */
    public function insertProductIfNotExists(int $productID, array $columnsAndValues): mixed
    {
        $columns = array();
        $valuesFormatedToSelect = array();
        
        foreach($columnsAndValues as $key => $value)
        {
            $formatedValue = "'{$value}'";
            array_push($valuesFormatedToSelect, "{$formatedValue} as {$key}");
            array_push($columns, $key);
        }

        $valuesToSelectImploded = implode(",", $valuesFormatedToSelect);
        $columnsImploded = implode(",", $columns);

        $stmt = $this->PDO->prepare("INSERT INTO {$this->tablename}({$columnsImploded}) SELECT * FROM (SELECT {$valuesToSelectImploded}) as tmp WHERE NOT EXISTS (SELECT id FROM {$this->tablename} WHERE id={$productID}) LIMIT 1");

        $stmt->execute();
        
        return $stmt->fetchColumn();
    }

    /**
     * Atualiza os produtos já existentes e, caso não exista, insere na base
     *
     * @param array $products
     * @return void
     */
    public function updateProducts(array $products): void
    {
        foreach($products as $key => $value)
        {
            try
            {
                $result = $this->insertProductIfNotExists($value["id"], $value);
                
                if($result === 1)
                {
                    unset($products[$key]);
                }
            }
            catch(PDOException $err)
            {
                $errorMessage = $err->getMessage() . " On product insert if no exist products";
                RegisterLog::RegisterLog('error', $errorMessage, 'update-info.log');
                continue;
            }
        }

        try
        {
            $this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->PDO->beginTransaction();

            foreach($products as $value)
            {
                $whereFilter = "id={$value["id"]}";
                $columnAndValue = array();
    
                array_walk(
                    $value,
                    function($item, $key) use (&$columnAndValue) {
                        array_push($columnAndValue, "{$key}='{$item}'");
                    }
                );
                
                $setColumnValues = implode(",", $columnAndValue);
                $this->PDO->exec("UPDATE {$this->tablename} SET {$setColumnValues} WHERE {$whereFilter}");
            }

            if($this->PDO->commit())
            {
                RegisterLog::RegisterLog('Info', 'Atualização dos produtos concluída com sucesso', 'update-info.log');
            }
            else
            {
                throw new Error("Erro ao commitar a transação");
            }
        }
        catch(PDOException $err)
        {
            $errorMessage = $err->getMessage() . " On product {$value["productId"]}";
            RegisterLog::RegisterLog('error', $errorMessage, 'update-info.log');
        }
    }

    /**
     * Verifica a quantidade de linhas para o resultado buscado via query string ou de todos os registros do banco
     *
     * @param array $filters
     * @return mixed
     */
    public function numberOfRows($filters): mixed
    {
        $stmt = "";
        
        if(!empty($filters))
        {
            $whereFilters = $this->performWhereFilters($filters);
            $stmt = $this->PDO->prepare("SELECT COUNT(*) FROM {$this->tablename} WHERE {$whereFilters}");
        }
        else
        {
            $stmt = $this->PDO->prepare("SELECT COUNT(*) FROM {$this->tablename}");
        }
        
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }

    /**
     * Cria a clausula where para realizar o filtro especificado na queryString passada
     *
     * @param array $filters
     * @return string
     */
    private function performWhereFilters($filters): string
    {
        $whereFilter = array();

        foreach($filters as $key => $value)
        {
            if($key === 'price' || $key === 'discount')
            {
                // Se o filtro for por valor, será repassado do controller separado por espaço em branco. Ex: bestPrice=50+26 será repassado como '50 26'
                $value1 = explode(" ", $value)[0];
                $value2 = explode(" ", $value)[1];
                $keyValue = ($key === 'price') ? 'price_not_formated' : 'promotional_price_not_formated';

                array_push(
                    $whereFilter,
                    "{$keyValue} BETWEEN {$value1} AND {$value2}"
                );
                continue;
            }

            if($key === 'name')
            {
                array_push($whereFilter, "{$key} LIKE '%{$value}%'");
                continue;
            }

            array_push(
                $whereFilter,
                $this->createWhereRegex($key, $value)
            );
        }
        
        return implode(" AND ", $whereFilter);
    }

    /**
     * Cria a regex responsável por encontrar os produtos pertencentes à categoria solicitada pois um produtos pode ter diversas categorias registradas
     *
     * @param string $columnName
     * @param string $valuesToRegexCreate
     * @return void
     */
    private function createWhereRegex($columnName, $valuesToCreateRegex)
    {
        return "{$columnName} REGEXP '" . implode("|", explode(" ", $valuesToCreateRegex)) . "'";
    }
}