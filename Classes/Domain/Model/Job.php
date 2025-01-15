<?php

declare(strict_types=1);

namespace B13\ContentSync\Domain\Model;

/*
 * This file is part of TYPO3 CMS-based extension "content-sync" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\ContentSync\Exception;
use Zumba\JsonSerializer\JsonSerializer;

class Job
{
    public const STATUS_WAITING = 0;
    public const STATUS_RUNNING = 1;
    public const STATUS_FINISHED = 2;
    public const STATUS_KILLED = 3;
    public const STATUS_FAILED = 4;
    // can be killed after 10m running
    public const KILLABLE_TIMELIMIT = 600;

    protected int $status = self::STATUS_WAITING;
    protected Configuration $configuration;
    protected \DateTime $startTime;
    protected \DateTime $endTime;
    protected \DateTime $createdTime;
    protected string $error = '';
    protected int $uid;

    public function __construct()
    {
        $this->createdTime = new \DateTime();
        $this->startTime = new \DateTime();
        $this->endTime = new \DateTime();
    }

    /**
     * @param string $error
     */
    public function fail(string $error): void
    {
        $this->error = $error;
        $this->status = self::STATUS_FAILED;
        $this->endTime = new \DateTime();
    }

    public function kill(): void
    {
        if (!$this->isKillable()) {
            throw new Exception('job not killable', 1600845225);
        }
        $this->status = self::STATUS_KILLED;
        $this->endTime = new \DateTime();
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @param Configuration $configuration
     */
    public function setConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function start(): void
    {
        $this->status = self::STATUS_RUNNING;
        $this->startTime = new \DateTime();
    }

    public function finish(): void
    {
        $this->status = self::STATUS_FINISHED;
        $this->endTime = new \DateTime();
    }

    public function toDatabaseRow(): array
    {
        $serializer = new JsonSerializer();
        $json = $serializer->serialize($this->getConfiguration());
        return [
            'status' => $this->status,
            'json_configuration' => $json,
            'created_time' => $this->createdTime->format('U'),
            'start_time' => $this->startTime->format('U'),
            'end_time' => $this->endTime->format('U'),
            'error' => $this->error,
        ];
    }

    public function fromDatabaseRow(array $databaseRow): Job
    {
        $serializer = new JsonSerializer();
        $this->configuration = $serializer->unserialize($databaseRow['json_configuration']);
        $this->startTime = $databaseRow['start_time'] > 0 ? new \DateTime('@' . $databaseRow['start_time']) : null;
        $this->endTime = $databaseRow['end_time'] > 0 ? new \DateTime('@' . $databaseRow['end_time']) : null;
        $this->createdTime = new \DateTime('@' . $databaseRow['created_time']);
        $this->error = (string)$databaseRow['error'];
        $this->uid = (int)$databaseRow['uid'];
        $this->status = (int)$databaseRow['status'];
        return $this;
    }

    public function isKillable(): bool
    {
        return
            $this->status === self::STATUS_RUNNING && $this->getExecutionTime() > self::KILLABLE_TIMELIMIT ||
            $this->status === self::STATUS_WAITING
        ;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return \DateTime
     */
    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    /**
     * @return \DateTime
     */
    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedTime(): \DateTime
    {
        return $this->createdTime;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    public function getExecutionTime(): int
    {
        if ($this->getStartTime() === null) {
            return 0;
        }
        if ($this->getEndTime() === null) {
            $now = new \DateTime();
            return (int)$now->format('U') - (int)$this->getStartTime()->format('U');
        }
        return (int)$this->getEndTime()->format('U') - (int)$this->getStartTime()->format('U');
    }
}
