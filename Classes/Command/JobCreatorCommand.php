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

use B13\ContentSync\Domain\Model\Configuration;
use B13\ContentSync\Domain\Model\Job;
use B13\ContentSync\Domain\Repository\JobRepository;
use B13\ContentSync\Domain\Validation\ConfigurationValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

final class JobCreatorCommand extends Command
{
    public function __construct(
        private readonly ExtensionConfiguration $extensionConfiguration,
        private readonly ConfigurationValidator $validator,
        private readonly JobRepository $jobRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $configuration = (new Configuration())->fromExtensionConfiguration($this->extensionConfiguration->get('content_sync'));
        $this->validator->assertValid($configuration);
        $job = new Job();
        $job->setConfiguration($configuration);
        $this->jobRepository->add($job);
        return 0;
    }
}
