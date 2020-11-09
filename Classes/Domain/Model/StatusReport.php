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

class StatusReport
{
    /**
     * @var Job
     */
    protected $job;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var bool
     */
    protected $configurationIsValid = false;

    /**
     * @var string
     */
    protected $configurationError = '';

    public function fromArray(array $properties): StatusReport
    {
        $this->job = $properties['job'];
        $this->configuration = $properties['configuration'];
        $this->configurationIsValid = $properties['configurationIsValid'];
        $this->configurationError = $properties['configurationError'];
        return $this;
    }

    /**
     * @return ?Job
     */
    public function getJob(): ?Job
    {
        return $this->job;
    }

    /**
     * @return ?Configuration
     */
    public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }

    /**
     * @return bool
     */
    public function isConfigurationIsValid(): bool
    {
        return $this->configurationIsValid;
    }

    /**
     * @return string
     */
    public function getConfigurationError(): string
    {
        return $this->configurationError;
    }

    public function isNewJobCreateable(): bool
    {
        return $this->configurationIsValid &&
            (
                $this->job === null ||
                !in_array($this->job->getStatus(), [Job::STATUS_WATING, Job::STATUS_RUNNING], true)
            );
    }
}
