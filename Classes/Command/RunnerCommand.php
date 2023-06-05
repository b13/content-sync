<?php

declare(strict_types=1);

namespace B13\ContentSync\Command;

/*
 * This file is part of TYPO3 CMS-based extension "content-sync" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\ContentSync\Domain\Repository\JobRepository;
use B13\ContentSync\Domain\Service\ProcessRunner;
use B13\ContentSync\Domain\Validation\ConfigurationValidator;
use B13\ContentSync\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunnerCommand extends Command
{
    protected ProcessRunner $processRunner;
    protected JobRepository $jobRepository;
    protected ConfigurationValidator $validator;

    public function __construct(
        ProcessRunner $processRunner,
        JobRepository $jobRepository,
        ConfigurationValidator $validator,
        string $name = null
    ) {
        parent::__construct($name);
        $this->processRunner = $processRunner;
        $this->jobRepository = $jobRepository;
        $this->validator = $validator;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $runningJob = $this->jobRepository->findOneRunning();
        if ($runningJob !== null) {
            return 0;
        }
        $job = $this->jobRepository->findOneWaiting();
        if ($job === null) {
            return 0;
        }
        $job->start();
        $this->jobRepository->updateJob($job);
        try {
            $configuration = $job->getConfiguration();
            $this->validator->assertMayRun($configuration);
            $sourceNode = $configuration->getSourceNode();
            if ($sourceNode->isLocal()) {
                $this->processRunner->localToRemote($configuration);
            } else {
                $this->processRunner->remoteToLocal($configuration);
            }
            // @extensionScannerIgnoreLine
            $job->finish();
            $this->jobRepository->updateJob($job);
            return 0;
        } catch (Exception $e) {
            $job->fail($e->getCode() . ' - ' . $e->getMessage());
            $this->jobRepository->updateJob($job);
            return 1;
        }
    }
}
