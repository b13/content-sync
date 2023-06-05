<?php

/**
 * Definitions for routes provided by EXT:content_sync
 */
return [
    'content-sync_create' => [
        'path' => '/ContentSync/job/create',
        'target' => B13\ContentSync\Backend\Controller\Ajax\JobController::class . '::create'
    ],
    'content-sync_kill' => [
        'path' => '/ContentSync/job/kill',
        'target' => B13\ContentSync\Backend\Controller\Ajax\JobController::class . '::kill'
    ],
    'content-sync_collect-garbage' => [
        'path' => '/ContentSync/collectGarbage',
        'target' => B13\ContentSync\Backend\Controller\Ajax\JobController::class . '::collectGarbage'
    ]
];
