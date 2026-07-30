<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use Throwable;

abstract class BaseService
{
    protected function fetch(string $sql): array
    {
        $query = $this->connection->prepare($sql);
        $query->execute([$this->companyId]);

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
