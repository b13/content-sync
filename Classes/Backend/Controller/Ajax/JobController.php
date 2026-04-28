<?php

declare(strict_types=1);

namespace B13\ContentSync\Backend\Controller\Ajax;

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
use B13\ContentSync\Exception;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

#[AsController]
final readonly class JobController
{
    public function __construct(
        private ViewFactoryInterface $viewFactory,
        private ExtensionConfiguration $extensionConfiguration,
        private ConfigurationValidator $validator,
        private JobRepository $jobRepository,
    ) {}

    public function create(ServerRequestInterface $request): Response
    {
        if (!$this->checkAccess()) {
            return (new Response())->withStatus(403);
        }
        $configuration = (new Configuration())->fromExtensionConfiguration($this->extensionConfiguration->get('content_sync'));
        try {
            $this->validator->assertValid($configuration);
            $job = new Job();
            $job->setConfiguration($configuration);
            $this->jobRepository->add($job);
            $viewFactoryData = new ViewFactoryData(
                templateRootPaths: ['EXT:content_sync/Resources/Private/Templates/'],
                partialRootPaths: [['EXT:content_sync/Resources/Private/Partials/']],
                request: $request,
            );
            $view = $this->viewFactory->create($viewFactoryData);
            $view->assign('job', $job);
            $return = [
                'flashMessage' => [
                    'title' => 'OK',
                    'message' => LocalizationUtility::translate('LLL:EXT:content_sync/Resources/Private/Language/locallang.xlf:flashMessage.job-created'),
                    'severity' => ContextualFeedbackSeverity::OK,
                ],
                'content' => $view->render('Ajax/Job/Create'),
            ];
        } catch (Exception) {
            $return = [
                'flashMessage' => [
                    'title' => 'ERROR',
                    'message' => LocalizationUtility::translate('LLL:EXT:content_sync/Resources/Private/Language/locallang.xlf:flashMessage.job-not-created'),
                    'severity' => ContextualFeedbackSeverity::ERROR,
                ],
            ];
        }

        return new JsonResponse($return);
    }

    public function kill(ServerRequestInterface $request): Response
    {
        if (!$this->checkAccess()) {
            return (new Response())->withStatus(403);
        }
        $job = $this->jobRepository->findOneLast();
        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: ['EXT:content_sync/Resources/Private/Templates/'],
            partialRootPaths: [['EXT:content_sync/Resources/Private/Partials/']],
            request: $request,
        );
        $view = $this->viewFactory->create($viewFactoryData);
        $view->assign('job', $job);
        try {
            $job->kill();
            $this->jobRepository->updateJob($job);
            $return = [
                'flashMessage' => [
                    'title' => 'OK',
                    'message' => LocalizationUtility::translate('LLL:EXT:content_sync/Resources/Private/Language/locallang.xlf:flashMessage.job-killed'),
                    'severity' => ContextualFeedbackSeverity::OK,
                ],
                'content' => $view->render('Ajax/Job/Kill'),
            ];
        } catch (Exception $e) {
            $return = [
                'flashMessage' => [
                    'title' => 'ERROR',
                    'message' => LocalizationUtility::translate('LLL:EXT:content_sync/Resources/Private/Language/locallang.xlf:flashMessage.job-not-killed'),
                    'severity' => ContextualFeedbackSeverity::ERROR,
                ],
                'content' => $view->render('Ajax/Job/Kill'),
            ];
        }

        return new JsonResponse($return);
    }

    private function checkAccess(): bool
    {
        return (bool)($this->getBackendUser()->getTSConfig()['options.']['enableContentSync'] ?? $this->getBackendUser()->isAdmin());
    }

    private function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
