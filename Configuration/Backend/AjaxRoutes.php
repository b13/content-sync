<?php

/**
 * Definitions for routes provided by EXT:l10n_translator
 */
return [
    'content-sync_create' => [
        'path' => '/ContentSync/job/create',
        'target' => B13\ContentSync\Backend\Controller\Ajax\JobController::class . '::create'
    ],
    'content-sync_kill' => [
        'path' => '/ContentSync/job/kill',
        'target' => B13\ContentSync\Backend\Controller\Ajax\JobController::class . '::kill'
    ]
];
