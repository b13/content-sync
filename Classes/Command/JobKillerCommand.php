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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class JobKillerCommand extends Command
{

    /**
     * @var JobRepository
     */
    protected $jobRepository;

    public function __construct(
        JobRepository $jobRepository,
        string $name = null
    ) {
        parent::__construct($name);
        $this->jobRepository = $jobRepository;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $job = $this->jobRepository->findOneLast();
        if ($job === null) {
            return 0;
        }
        $job->kill();
        $this->jobRepository->updateJob($job);
        return 0;
    }
}
