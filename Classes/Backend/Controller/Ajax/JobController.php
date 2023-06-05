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
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class JobController implements SingletonInterface
{
    protected ExtensionConfiguration $extensionConfiguration;
    protected ConfigurationValidator $validator;
    protected JobRepository $jobRepository;

    public function __construct(
        ExtensionConfiguration $extensionConfiguration,
        ConfigurationValidator $validator,
        JobRepository $jobRepository
    ) {
        $this->extensionConfiguration = $extensionConfiguration;
        $this->validator = $validator;
        $this->jobRepository = $jobRepository;
    }

    public function create(ServerRequestInterface $request): Response
    {
        if (!$this->checkAccess()) {
            return (new Response())->withStatus(403);
        }
        $configuration = (new Configuration())->fromExtensionConfiguration($this->extensionConfiguration->get('content_sync'));
        $view = $this->getFluidTemplateObject('Create');
        try {
            $this->validator->assertValid($configuration);
            $job = new Job();
            $job->setConfiguration($configuration);
            $this->jobRepository->add($job);
            $view->assign('job', $job);
            $return = [
                'flashMessage' => [
                    'title' => 'OK',
                    'message' => LocalizationUtility::translate('LLL:EXT:content_sync/Resources/Private/Language/locallang.xlf:flashMessage.job-created'),
                    'severity' => FlashMessage::OK,
                ],
                'content' => $view->render(),
            ];
        } catch (Exception $e) {
            $return = [
                'flashMessage' => [
                    'title' => 'ERROR',
                    'message' => LocalizationUtility::translate('LLL:EXT:content_sync/Resources/Private/Language/locallang.xlf:flashMessage.job-not-created'),
                    'severity' => FlashMessage::ERROR,
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
        $view = $this->getFluidTemplateObject('Kill');
        $job = $this->jobRepository->findOneLast();
        $view->assign('job', $job);
        try {
            $job->kill();
            $this->jobRepository->updateJob($job);
            $return = [
                'flashMessage' => [
                    'title' => 'OK',
                    'message' => LocalizationUtility::translate('LLL:EXT:content_sync/Resources/Private/Language/locallang.xlf:flashMessage.job-killed'),
                    'severity' => FlashMessage::OK,
                ],
                'content' => $view->render(),
            ];
        } catch (Exception $e) {
            $return = [
                'flashMessage' => [
                    'title' => 'ERROR',
                    'message' => LocalizationUtility::translate('LLL:EXT:content_sync/Resources/Private/Language/locallang.xlf:flashMessage.job-not-killed'),
                    'severity' => FlashMessage::ERROR,
                ],
                'content' => $view->render(),
            ];
        }

        return new JsonResponse($return);
    }

    protected function getFluidTemplateObject(string $filename): StandaloneView
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setPartialRootPaths(['EXT:content_sync/Resources/Private/Partials']);
        $view->setTemplateRootPaths(['EXT:content_sync/Resources/Private/Templates/Ajax/Job']);
        $view->setTemplate($filename);

        return $view;
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected function checkAccess(): bool
    {
        return (bool)($this->getBackendUser()->getTSConfig()['options.']['enableContentSync'] ?? $this->getBackendUser()->isAdmin());
    }
}
