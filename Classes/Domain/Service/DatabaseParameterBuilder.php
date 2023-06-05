<?php

declare(strict_types=1);

namespace B13\ContentSync\Domain\Service;

/*
 * This file is part of TYPO3 CMS-based extension "content-sync" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\ContentSync\Domain\Model\Configuration;
use Helhum\Typo3Console\Database\Schema\TableMatcher;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;

class DatabaseParameterBuilder implements SingletonInterface
{
    private const DATABASE_CONNECTION_NAME = 'Default';
    protected Connection $connection;

    public function __construct(ConnectionPool $connectionPool)
    {
        $this->connection = $connectionPool->getConnectionByName(self::DATABASE_CONNECTION_NAME);
    }

    public function buildTablesExclude(Configuration $configuration)
    {
        $excludeTables = [];
        if (!empty($configuration->getDatabaseTables())) {
            $excludeTables = array_diff(
                $this->getAllTables(),
                $this->getMatchedTables($configuration->getDatabaseTables())
            );
        } elseif (!empty($configuration->getExcludeDatabaseTables())) {
            $excludeTables = $this->getMatchedTables($configuration->getExcludeDatabaseTables());
        }
        if (empty($excludeTables)) {
            return '';
        }
        return '-e ' . implode(' -e ', $excludeTables);
    }

    protected function getMatchedTables(array $tables): array
    {
        return (new TableMatcher())->match($this->connection, ...$tables);
    }

    protected function getAllTables(): array
    {
        return $this->connection->getSchemaManager()->listTableNames();
    }
}
