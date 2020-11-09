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

use TYPO3\CMS\Core\Utility\GeneralUtility;

class Configuration
{
    protected $databaseTables = [];

    protected $excludeDatabaseTables = [];

    protected $syncFiles = [];

    /**
     * @var Node
     */
    protected $targetNode;

    /**
     * @var Node
     */
    protected $sourceNode;

    public function fromExtensionConfiguration(array $extensionConfiguration): Configuration
    {
        $this->databaseTables = GeneralUtility::trimExplode(',', $extensionConfiguration['configuration']['databaseTables'], true);
        $this->excludeDatabaseTables = GeneralUtility::trimExplode(',', $extensionConfiguration['configuration']['excludeDatabaseTables'], true);
        $this->syncFiles = GeneralUtility::trimExplode(',', $extensionConfiguration['configuration']['syncFiles'], true);
        $this->targetNode = (new Node())->fromArray($extensionConfiguration['targetNode']);
        $this->sourceNode = (new Node())->fromArray($extensionConfiguration['sourceNode']);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDatabaseTables(): array
    {
        return $this->databaseTables;
    }

    /**
     * @return string[]
     */
    public function getExcludeDatabaseTables(): array
    {
        return $this->excludeDatabaseTables;
    }

    /**
     * @return string[]
     */
    public function getSyncFiles(): array
    {
        return $this->syncFiles;
    }

    public function getTargetNode(): Node
    {
        return $this->targetNode;
    }

    public function getSourceNode(): Node
    {
        return $this->sourceNode;
    }

    public function getRemoteNode(): ?Node
    {
        if (!$this->getTargetNode()->isLocal()) {
            return $this->getTargetNode();
        }
        if (!$this->getSourceNode()->isLocal()) {
            return $this->getSourceNode();
        }
        return null;
    }
}
