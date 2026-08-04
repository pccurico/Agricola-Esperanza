<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use Throwable;

abstract class BaseService
{
    protected readonly PDO $connection;
    protected readonly int $companyId;

    protected function fetch(string $sql, array $params = []): array
    {
        $query = $this->connection->prepare($sql);
        $query->execute($params === [] ? [$this->companyId] : $params);

        return $query->fetchAll();
    }

    protected function fetchRows(string $sql, array $params = []): array
    {
        $query = $this->connection->prepare($sql);
        $query->execute($params);

        return $query->fetchAll();
    }

    protected function execute(string $sql, array $parameters): void
    {
        $this->connection->prepare($sql)->execute($parameters);
    }

    protected function transaction(PDO $connection, callable $callback): mixed
    {
        $connection->beginTransaction();
        try {
            $result = $callback();
            $connection->commit();

            return $result;
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }
}
