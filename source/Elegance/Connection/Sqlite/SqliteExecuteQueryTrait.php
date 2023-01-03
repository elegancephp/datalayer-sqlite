<?php

namespace Elegance\Connection\Sqlite;

use Elegance\Dir;
use Error;
use Exception;
use PDO;
use PDOException;

trait SqliteExecuteQueryTrait
{
    protected $instancePDO;

    /** Executa uma query */
    function executeQuery(mixed $queryString, array $queryData = []): mixed
    {
        list($queryString, $queryData) = $this->getQueryArray(func_get_args());

        try {
            $pdoQuery = $this->pdo()->prepare($queryString);
            if (!$pdoQuery)
                throw new Error("[$queryString]");

            if (!$pdoQuery->execute($queryData)) {
                $error = $pdoQuery->errorInfo();
                $error = array_pop($error);
                throw new Error("[$queryString] [$error]");
            }
        } catch (Error | Exception | PDOException $e) {
            throw new Error($e->getMessage());
        }

        return match (explode(' ', $queryString)[0]) {
            'DELETE' => true,
            'INSERT' => $this->pdo()->lastInsertId(),
            'PRAGMA', 'SELECT' => $pdoQuery->fetchAll(PDO::FETCH_ASSOC),
            'UPDATE' => true,
            default => $pdoQuery
        };
    }

    /** Executa uma lista de  querys */
    function executeQueryList(array $queryList = []): array
    {
        foreach ($queryList as &$query) {
            $query = $this->getQueryArray($query);
            $query = $this->executeQuery(...$query);
        }

        return $queryList;
    }

    /** Retorna a instancia PDO da conexão */
    protected function pdo(): PDO
    {
        if (is_array($this->instancePDO)) {
            try {
                Dir::create(path($this->data['path'], $this->data['file']));
                $this->instancePDO = new PDO(...(array) $this->instancePDO);
                $this->instancePDO->sqliteCreateFunction('md5', 'md5', 1);
                $this->instancePDO->sqliteCreateFunction('concat', 'concat', -1);
                $this->instancePDO->sqliteCreateFunction('ck', 'ck', 1);
            } catch (Error | Exception | PDOException $e) {
                throw new Error($e->getMessage());
            }
        }
        return $this->instancePDO;
    }
}
