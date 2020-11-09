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
use B13\ContentSync\Exception;
use Symfony\Component\Process\Process;
use TYPO3\CMS\Core\SingletonInterface;

class ProcessRunner implements SingletonInterface
{
    /**
     * @var DatabaseParameterBuilder
     */
    protected $databaseParameterBuilder;

    public function __construct(DatabaseParameterBuilder $databaseParameterBuilder)
    {
        $this->databaseParameterBuilder = $databaseParameterBuilder;
    }

    public function localToRemote(Configuration $configuration): void
    {
        $remoteNode = $configuration->getTargetNode();
        $localNode = $configuration->getSourceNode();
        $tmpFileName = tempnam('/tmp', 'content-sync-dbdump');
        $commands = [
            $localNode->getBin() . ' database:export ' . $this->databaseParameterBuilder->buildTablesExclude($configuration) . ' | gzip > ' . $tmpFileName,
            'scp ' . $tmpFileName . ' ' . $remoteNode->getConnection() . ':' . $tmpFileName,
            'ssh ' . $remoteNode->getConnection() . ' "zcat ' . $tmpFileName . '|' . $remoteNode->getBin() . ' database:import"',
            'ssh ' . $remoteNode->getConnection() . ' "' . $remoteNode->getBin() . ' cache:flushgroups pages"',
            'ssh ' . $remoteNode->getConnection() . ' "rm ' . $tmpFileName . '"',
            'if [ -e ' . $tmpFileName . ']; then rm ' . $tmpFileName . '; fi'
        ];
        foreach ($configuration->getSyncFiles() as $file) {
            $file = rtrim($file, '/');
            $commands[] = 'rsync -a --delete --omit-dir-times --no-owner --no-group ' . $localNode->getBasePath() . $file . '/ ' . $remoteNode->getConnection() . ':' . $remoteNode->getBasePath() . $file;
        }
        foreach ($commands as $command) {
            $this->exec($command);
        }
    }

    public function remoteToLocal(Configuration $configuration): void
    {
        $remoteNode = $configuration->getSourceNode();
        $localNode = $configuration->getTargetNode();
        $tmpFileName = tempnam('/tmp', 'content-sync-dbdump');
        $commands = [
            'ssh ' . $remoteNode->getConnection() . ' "' . $remoteNode->getBin() . ' database:export ' . $this->databaseParameterBuilder->buildTablesExclude($configuration) . ' | gzip > ' . $tmpFileName . '"',
            'scp ' . $remoteNode->getConnection() . ':' . $tmpFileName . ' ' . $tmpFileName,
            'zcat ' . $tmpFileName . ' | ' . $localNode->getBin() . ' database:import',
            $localNode->getBin() . ' cache:flushgroups pages',
            'ssh ' . $remoteNode->getConnection() . ' "rm ' . $tmpFileName . '"',
            'if [ -e ' . $tmpFileName . ']; then rm ' . $tmpFileName . '; fi'
        ];
        foreach ($configuration->getSyncFiles() as $file) {
            $file = rtrim($file, '/');
            $commands[] = 'rsync -a --delete --omit-dir-times --no-owner --no-group ' . $remoteNode->getConnection() . ':' . $remoteNode->getBasePath() . $file . '/ ' . $localNode->getBasePath() . $file;
        }
        foreach ($commands as $command) {
            $this->exec($command);
        }
    }

    protected function exec(string $cmd): void
    {
        $process = Process::fromShellCommandline($cmd);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new Exception('cannot exec command ' . $cmd . ' with error ' . $process->getErrorOutput(), 1600757440);
        }
    }
}
