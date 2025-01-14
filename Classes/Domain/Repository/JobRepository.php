<?php

declare(strict_types=1);

namespace B13\ContentSync\Domain\Repository;

/*
 * This file is part of TYPO3 CMS-based extension "content-sync" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\ContentSync\Domain\Model\Job;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;

class JobRepository implements SingletonInterface
{
    public const TABLE = 'tx_contentsync_job';

    protected Connection $databaseConnection;

    public function __construct(ConnectionPool $connectionPool)
    {
        $this->databaseConnection = $connectionPool->getConnectionForTable(self::TABLE);
    }

    public function add(Job $job): void
    {
        $this->databaseConnection->insert(self::TABLE, $job->toDatabaseRow());
    }

    public function updateJob(Job $job): void
    {
        $this->databaseConnection->update(self::TABLE, $job->toDatabaseRow(), ['uid' => $job->getUid()]);
    }

    public function findOneLast(): ?Job
    {
        $queryBuilder = $this->databaseConnection->createQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from(self::TABLE)
            ->orderBy('created_time', 'DESC')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();
        if ($row === []) {
            return null;
        }
        return (new Job())->fromDatabaseRow($row);
    }

    public function findOneRunning(): ?Job
    {
        $queryBuilder = $this->databaseConnection->createQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq(
                    'status',
                    $queryBuilder->createNamedParameter(Job::STATUS_RUNNING, Connection::PARAM_INT)
                )
            )
            ->orderBy('created_time', 'ASC')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAllAssociative();
        if ($row === []) {
            return null;
        }
        return (new Job())->fromDatabaseRow($row);
    }

    public function findOneWaiting(): ?Job
    {
        $queryBuilder = $this->databaseConnection->createQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq(
                    'status',
                    $queryBuilder->createNamedParameter(Job::STATUS_WAITING, Connection::PARAM_INT)
                )
            )
            ->orderBy('created_time', 'ASC')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAllAssociative();

        if ($row === []) {
            return null;
        }
        return (new Job())->fromDatabaseRow($row);
    }

    public function findStaleJobs(): array
    {
        $queryBuilder = $this->databaseConnection->createQueryBuilder();
        $timeLimit = (new \DateTime('-' . Job::KILLABLE_TIMELIMIT . ' seconds'))->getTimestamp();
        $res = $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->lte(
                    'created_time',
                    $queryBuilder->createNamedParameter($timeLimit, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->in(
                    'status',
                    $queryBuilder->createNamedParameter([Job::STATUS_WAITING, Job::STATUS_RUNNING], Connection::PARAM_INT_ARRAY)
                )
            )
            ->executeQuery();

        $jobs = [];
        while ($row = $res->fetchAssociative()) {
            $jobs[] = (new Job())->fromDatabaseRow($row);
        }
        return $jobs;
    }
}
