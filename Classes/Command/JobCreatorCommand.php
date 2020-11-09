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

class JobCreatorCommand extends Command
{
    /**
     * @var ExtensionConfiguration
     */
    protected $extensionConfiguration;

    /**
     * @var ConfigurationValidator
     */
    protected $validator;

    /**
     * @var JobRepository
     */
    protected $jobRepository;

    public function __construct(
        ExtensionConfiguration $extensionConfiguration,
        ConfigurationValidator $validator,
        JobRepository $jobRepository,
        string $name = null
    ) {
        parent::__construct($name);
        $this->extensionConfiguration = $extensionConfiguration;
        $this->validator = $validator;
        $this->jobRepository = $jobRepository;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
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
