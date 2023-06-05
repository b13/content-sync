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

use B13\ContentSync\Domain\Factory\StatusReportFactory;
use B13\ContentSync\Domain\Model\Job;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class StatusReportCommand extends Command
{
    protected StatusReportFactory $statusReportFactory;

    public function __construct(StatusReportFactory $statusReportFactory, string $name = null)
    {
        parent::__construct($name);
        $this->statusReportFactory = $statusReportFactory;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $llPrefix = 'LLL:EXT:content_sync/Resources/Private/Language/locallang.xlf:statusReport.';
        $statusReport = $this->statusReportFactory->build();
        // configuration
        if ($statusReport->isConfigurationIsValid()) {
            $output->writeln(LocalizationUtility::translate($llPrefix . 'current-configuration'));
            $output->writeln($statusReport->getConfiguration()->getSourceNode()->getConnection() . ' -> ' . $statusReport->getConfiguration()->getTargetNode()->getConnection());
        } else {
            $output->writeln(LocalizationUtility::translate($llPrefix . 'invalid-configuration'));
            $output->writeln($statusReport->getConfigurationError());
        }
        // job
        if ($statusReport->getJob() !== null) {
            $output->writeln(
                LocalizationUtility::translate($llPrefix . 'job-status') . ': ' .
                LocalizationUtility::translate($llPrefix . 'job-status.' . $statusReport->getJob()->getStatus()) . ' (' .
                $statusReport->getJob()->getConfiguration()->getSourceNode()->getConnection() . ' -> ' .
                $statusReport->getJob()->getConfiguration()->getTargetNode()->getConnection() . ')'
            );
            switch ($statusReport->getJob()->getStatus()) {
                case Job::STATUS_WATING:
                    $output->writeln(
                        LocalizationUtility::translate($llPrefix . 'job.created-at') . ': ' .
                        $statusReport->getJob()->getCreatedTime()->format(LocalizationUtility::translate($llPrefix . 'date-format'))
                    );
                    break;
                case Job::STATUS_RUNNING:
                    $output->writeln(
                        LocalizationUtility::translate($llPrefix . 'job.running-since') . ': ' .
                        $statusReport->getJob()->getStartTime()->format(LocalizationUtility::translate($llPrefix . 'date-format')) . ' (' .
                        LocalizationUtility::translate($llPrefix . 'job.execution-time') . ': ' .
                        $statusReport->getJob()->getExecutionTime() . ' ' .
                        LocalizationUtility::translate($llPrefix . 'seconds') . ')'
                    );
                    if ($statusReport->getJob()->isKillable()) {
                        $output->writeln(LocalizationUtility::translate($llPrefix . 'job.kill'));
                    }
                    break;
                case Job::STATUS_FINISHED:
                case Job::STATUS_KILLED:
                case Job::STATUS_FAILED:
                    $output->writeln(
                        LocalizationUtility::translate($llPrefix . 'job.finished-at') . ': ' .
                        $statusReport->getJob()->getEndTime()->format(LocalizationUtility::translate($llPrefix . 'date-format')) . ' (' .
                        LocalizationUtility::translate($llPrefix . 'job.execution-time') . ': ' .
                        $statusReport->getJob()->getExecutionTime() . ' ' .
                        LocalizationUtility::translate($llPrefix . 'seconds') . ')'
                    );
                    if ($statusReport->getJob()->getStatus() === Job::STATUS_FAILED) {
                        $output->writeln($statusReport->getJob()->getError());
                    }
                    break;
                default:
                    break;
            }
        } else {
            $output->writeln(LocalizationUtility::translate($llPrefix . 'no-jobs'));
        }
        return 0;
    }
}
