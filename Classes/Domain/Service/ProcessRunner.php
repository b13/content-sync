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
use B13\ContentSync\Event\BeforeProcessRunnerExecutesCommandsEvent;
use B13\ContentSync\Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

final readonly class ProcessRunner
{
    public function __construct(
        private DatabaseParameterBuilder $databaseParameterBuilder,
        private LoggerInterface $logger,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function localToRemote(Configuration $configuration): void
    {
        $remoteNode = $configuration->getTargetNode();
        $localNode = $configuration->getSourceNode();
        $tmpFileName = tempnam('/tmp', 'content-sync-dbdump');
        $tmpFileNameRemote = 'content-sync-dbdump' . time();
        $commands = [
            $localNode->getBin() . ' database:export ' . $this->databaseParameterBuilder->buildTablesExclude($configuration) . ' | gzip > ' . $tmpFileName,
            'scp ' . $tmpFileName . ' ' . $remoteNode->getConnection() . ':' . $tmpFileNameRemote,
            'ssh ' . $remoteNode->getConnection() . ' "zcat ' . $tmpFileNameRemote . '|' . $remoteNode->getBin() . ' database:import"',
            'ssh ' . $remoteNode->getConnection() . ' "' . $remoteNode->getBin() . ' ' . $this->getFlushPageCacheArguments() . '"',
            'ssh ' . $remoteNode->getConnection() . ' "rm ' . $tmpFileNameRemote . '"',
            'if [ -e ' . $tmpFileName . ']; then rm ' . $tmpFileName . '; fi',
        ];
        foreach ($configuration->getSyncFiles() as $file) {
            $file = rtrim($file, '/');
            $commands[] = 'rsync -a --delete --omit-dir-times --no-owner --no-group ' . $localNode->getBasePath() . $file . '/ ' . $remoteNode->getConnection() . ':' . $remoteNode->getBasePath() . $file;
        }
        $beforeProcessRunnnerExecuteCommands = new BeforeProcessRunnerExecutesCommandsEvent($configuration, $commands);
        $this->eventDispatcher->dispatch($beforeProcessRunnnerExecuteCommands);
        $commands = $beforeProcessRunnnerExecuteCommands->commands;
        foreach ($commands as $command) {
            $this->exec($command, $beforeProcessRunnnerExecuteCommands->timeoutPerProcess);
        }
    }

    public function remoteToLocal(Configuration $configuration): void
    {
        $remoteNode = $configuration->getSourceNode();
        $localNode = $configuration->getTargetNode();
        $tmpFileName = tempnam('/tmp', 'content-sync-dbdump');
        $tmpFileNameRemote = 'content-sync-dbdump' . time();
        $commands = [
            'ssh ' . $remoteNode->getConnection() . ' "' . $remoteNode->getBin() . ' database:export ' . $this->databaseParameterBuilder->buildTablesExclude($configuration) . ' | gzip > ' . $tmpFileNameRemote . '"',
            'scp ' . $remoteNode->getConnection() . ':' . $tmpFileNameRemote . ' ' . $tmpFileName,
            'zcat ' . $tmpFileName . ' | ' . $localNode->getBin() . ' database:import',
            $localNode->getBin() . ' ' . $this->getFlushPageCacheArguments(),
            'ssh ' . $remoteNode->getConnection() . ' "rm ' . $tmpFileNameRemote . '"',
            'if [ -e ' . $tmpFileName . ']; then rm ' . $tmpFileName . '; fi',
        ];
        foreach ($configuration->getSyncFiles() as $file) {
            $file = rtrim($file, '/');
            $commands[] = 'rsync -a --delete --omit-dir-times --no-owner --no-group ' . $remoteNode->getConnection() . ':' . $remoteNode->getBasePath() . $file . '/ ' . $localNode->getBasePath() . $file;
        }
        $beforeProcessRunnnerExecuteCommands = new BeforeProcessRunnerExecutesCommandsEvent($configuration, $commands);
        $this->eventDispatcher->dispatch($beforeProcessRunnnerExecuteCommands);
        $commands = $beforeProcessRunnnerExecuteCommands->commands;
        foreach ($commands as $command) {
            $this->exec($command, $beforeProcessRunnnerExecuteCommands->timeoutPerProcess);
        }
    }

    private function exec(string $cmd, int $timeout): void
    {
        $this->logger->debug($cmd);
        $process = Process::fromShellCommandline(command: $cmd, timeout: $timeout);
        try {
            $process->run();
        } catch (RuntimeException $e) {
            throw new Exception('process runtime exception: ' . $e->getMessage() . ' - ' . $e->getCode(), 1780919317);
        }
        if (!$process->isSuccessful()) {
            throw new Exception('cannot exec command ' . $cmd . ' with error ' . $process->getErrorOutput(), 1600757440);
        }
    }

    private function getFlushPageCacheArguments(): string
    {
        return 'cache:flush --group pages';
    }
}
