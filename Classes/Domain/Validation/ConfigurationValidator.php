<?php

declare(strict_types=1);

namespace B13\ContentSync\Domain\Validation;

/*
 * This file is part of TYPO3 CMS-based extension "content-sync" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\ContentSync\Domain\Model\Configuration;
use B13\ContentSync\Domain\Model\Node;
use B13\ContentSync\Exception;
use Symfony\Component\Process\Process;
use TYPO3\CMS\Core\SingletonInterface;

class ConfigurationValidator implements SingletonInterface
{
    public function assertMayRun(Configuration $configuration): void
    {
        $this->assertValid($configuration);
        $this->assertRemoteIsValid($configuration->getRemoteNode());
    }

    public function assertRemoteIsValid(Node $remoteNode): void
    {
        // test ssh connection
        $process = Process::fromShellCommandline('ssh -o BatchMode=yes -o ConnectTimeout=5 ' . $remoteNode->getConnection() . ' echo ok');
        $process->run();
        if (!$process->isSuccessful()) {
            throw new Exception('cannot establish ssh connection to remote node', 1600765840);
        }
        // remote typo3 bin exists
        $process = Process::fromShellCommandline('ssh -o BatchMode=yes -o ConnectTimeout=5 ' . $remoteNode->getConnection() . ' ls ' . $remoteNode->getBin());
        $process->run();
        if (!$process->isSuccessful()) {
            throw new Exception('typo3cms bin not found at remote node', 1600765841);
        }
        // remote basePath exists
        $process = Process::fromShellCommandline('ssh -o BatchMode=yes -o ConnectTimeout=5 ' . $remoteNode->getConnection() . ' ls -d ' . $remoteNode->getBasePath());
        $process->run();
        if (!$process->isSuccessful()) {
            throw new Exception('basePath not found at remote node', 1600765842);
        }
    }

    public function assertValid(Configuration $configuration): void
    {
        $sourceNode = $configuration->getSourceNode();
        $targetNode = $configuration->getTargetNode();
        if ($targetNode->isLocal() && $sourceNode->isLocal() || !$targetNode->isLocal() && !$sourceNode->isLocal()) {
            throw new Exception('one Node must be local', 1600765838);
        }
        if ($sourceNode->isLocal()) {
            $remoteNode = $targetNode;
            $localNode = $sourceNode;
        } else {
            $remoteNode = $sourceNode;
            $localNode = $targetNode;
        }
        if ($remoteNode->getConnection() === '') {
            throw new Exception('connection of remote node cannot be empty', 1600765839);
        }

        // local typo3 bin exists
        $process = Process::fromShellCommandline('ls ' . $localNode->getBin());
        $process->run();
        if (!$process->isSuccessful()) {
            throw new Exception('typo3cms bin not found at local node', 1600765843);
        }
        // remote basePath exists
        $process = Process::fromShellCommandline('ls -d ' . $localNode->getBasePath());
        $process->run();
        if (!$process->isSuccessful()) {
            throw new Exception('basePath not found at local node', 1600765844);
        }
    }
}
