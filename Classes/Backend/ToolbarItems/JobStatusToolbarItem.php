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
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class JobStatusToolbarItem implements ToolbarItemInterface
{
    protected StatusReportFactory $statusReportFactory;

    public function __construct(StatusReportFactory $statusReportFactory, PageRenderer $pageRenderer)
    {
        $this->statusReportFactory = $statusReportFactory;
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/ContentSync/ContentSync');
    }

    public function checkAccess(): bool
    {
        return (bool)($this->getBackendUser()->getTSConfig()['options.']['enableContentSync'] ?? $this->getBackendUser()->isAdmin() ?? false);
    }

    public function getItem(): string
    {
        $view = $this->getFluidTemplateObject('JobStatusToolbarItem.html');
        return $view->render();
    }
    public function hasDropDown(): bool
    {
        return true;
    }

    public function getDropDown(): string
    {
        $view = $this->getFluidTemplateObject('JobStatusToolbarItemDropDown.html');
        $statusReport = $this->statusReportFactory->build();
        $view->assign('statusReport', $statusReport);
        return $view->render();
    }

    public function getAdditionalAttributes(): array
    {
        return [];
    }

    public function getIndex(): int
    {
        return 50;
    }

    /**
     * Returns a new standalone view, shorthand function
     *
     * @param string $filename Which templateFile should be used.
     * @return StandaloneView
     */
    protected function getFluidTemplateObject(string $filename): StandaloneView
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setLayoutRootPaths(['EXT:backend/Resources/Private/Layouts']);
        $view->setPartialRootPaths(['EXT:backend/Resources/Private/Partials/ToolbarItems', 'EXT:content_sync/Resources/Private/Partials']);

        $templateRootPaths = ['EXT:content_sync/Resources/Private/Templates/ToolbarItems'];
        // @todo: remove when v11 was dropped
        if ((new Typo3Version())->getMajorVersion() < 12) {
            $templateRootPaths = ['EXT:content_sync/Resources/Private/Templates/ToolbarItemsV11'];
            $view->getRequest()->setControllerExtensionName('ContentSync');
        }

        $view->setTemplateRootPaths($templateRootPaths);
        $view->setTemplate($filename);

        return $view;
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
