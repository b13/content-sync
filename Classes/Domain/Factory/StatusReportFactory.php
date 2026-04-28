<?php

declare(strict_types=1);

namespace B13\ContentSync\Domain\Factory;

/*
 * This file is part of TYPO3 CMS-based extension "content-sync" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\ContentSync\Domain\Model\Configuration;
use B13\ContentSync\Domain\Model\StatusReport;
use B13\ContentSync\Domain\Repository\JobRepository;
use B13\ContentSync\Domain\Validation\ConfigurationValidator;
use B13\ContentSync\Exception;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

final readonly class StatusReportFactory
{
    public function __construct(
        private ConfigurationValidator $configurationValidator,
        private ExtensionConfiguration $extensionConfiguration,
        private JobRepository $jobRepository
    ) {}

    public function build(): StatusReport
    {
        $job = $this->jobRepository->findOneLast();
        $configuration = (new Configuration())->fromExtensionConfiguration($this->extensionConfiguration->get('content_sync'));
        try {
            $this->configurationValidator->assertValid($configuration);
            $configurationIsValid = true;
            $configurationError = '';
        } catch (Exception $e) {
            $configurationIsValid = false;
            $configurationError = $e->getMessage();
        }
        return (new StatusReport())->fromArray([
            'job' => $job,
            'configurationError' => $configurationError,
            'configurationIsValid' => $configurationIsValid,
            'configuration' => $configuration,
        ]);
    }
}
