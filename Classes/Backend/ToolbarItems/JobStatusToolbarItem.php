<?php

declare(strict_types=1);

namespace B13\ContentSync\Backend\ToolbarItems;

/*
 * This file is part of TYPO3 CMS-based extension "content-sync" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\ContentSync\Domain\Factory\StatusReportFactory;
use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;

final readonly class JobStatusToolbarItem implements ToolbarItemInterface
{
    public function __construct(
        private ViewFactoryInterface $viewFactory,
        private StatusReportFactory $statusReportFactory,
        PageRenderer $pageRenderer
    ) {
        $pageRenderer->loadJavaScriptModule('@b13/content-sync/content-sync.js');
    }

    public function checkAccess(): bool
    {
        return (bool)($this->getBackendUser()->getTSConfig()['options.']['enableContentSync'] ?? $this->getBackendUser()->isAdmin());
    }

    public function getItem(): string
    {
        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: ['EXT:content_sync/Resources/Private/Templates/'],
            partialRootPaths: ['EXT:backend/Resources/Private/Partials/ToolbarItems', 'EXT:content_sync/Resources/Private/Partials'],
            layoutRootPaths: ['EXT:backend/Resources/Private/Layouts'],
        );
        $view = $this->viewFactory->create($viewFactoryData);
        return $view->render('ToolbarItems/JobStatusToolbarItem');
    }
    public function hasDropDown(): bool
    {
        return true;
    }

    public function getDropDown(): string
    {
        $statusReport = $this->statusReportFactory->build();
        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: ['EXT:content_sync/Resources/Private/Templates/'],
            partialRootPaths: ['EXT:backend/Resources/Private/Partials/ToolbarItems', 'EXT:content_sync/Resources/Private/Partials'],
            layoutRootPaths: ['EXT:backend/Resources/Private/Layouts'],
        );
        $view = $this->viewFactory->create($viewFactoryData);
        $view->assign('statusReport', $statusReport);
        return $view->render('ToolbarItems/JobStatusToolbarItemDropDown');
    }

    public function getAdditionalAttributes(): array
    {
        return [];
    }

    public function getIndex(): int
    {
        return 50;
    }

    private function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
