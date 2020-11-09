<?php

defined('TYPO3_MODE') or die();

call_user_func(static function () {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][1600777530] = \B13\ContentSync\Backend\ToolbarItems\JobStatusToolbarItem::class;
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    $iconRegistry->registerIcon(
        'b13-content-sync-toolbar',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        [
            'source' => 'EXT:content_sync/Resources/Public/Icons/ContentSync.svg'
        ]
    );
    $iconRegistry->registerIcon(
        'b13-content-sync-configuration',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        [
            'source' => 'EXT:content_sync/Resources/Public/Icons/ConfigurationCheck.svg'
        ]
    );
    $iconRegistry->registerIcon(
        'b13-content-sync-status',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        [
            'source' => 'EXT:content_sync/Resources/Public/Icons/SynchronisationHistory.svg'
        ]
    );
});
