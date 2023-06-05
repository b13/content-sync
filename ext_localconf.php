<?php

defined('TYPO3') or die();

call_user_func(static function () {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][1600777530] = \B13\ContentSync\Backend\ToolbarItems\JobStatusToolbarItem::class;
});
