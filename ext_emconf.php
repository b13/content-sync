<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Content Sync',
    'description' => 'Sync Database Tables and Files between two TYPO3 Installations',
    'category' => 'misc',
    'author' => 'b13 GmbH',
    'author_email' => 'typo3@b13.com',
    'author_company' => 'b13 GmbH',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => true,
    'version' => '3.1.0',
    'constraints' => [
        'depends' => ['typo3' => '13.4.0-14.99.99'],
        'conflicts' => [],
        'suggests' => [],
    ],
];
