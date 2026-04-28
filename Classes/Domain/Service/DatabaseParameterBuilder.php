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
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;

final readonly class DatabaseParameterBuilder implements SingletonInterface
{
    public function __construct(
        private ConnectionPool $connectionPool,
    ) {}

    public function buildTablesExclude(Configuration $configuration): string
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

    private function getMatchedTables(array $tables): array
    {
        return (new TableMatcher())->match($this->connectionPool->getConnectionByName('Default'), ...$tables);
    }

    private function getAllTables(): array
    {
        return $this->connectionPool->getConnectionByName('Default')->createSchemaManager()->listTableNames();
    }
}
