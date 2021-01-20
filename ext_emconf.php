<?php

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
    'version' => '1.0.2',
    'constraints' => [
        'depends' => ['typo3' => '10.4.0-10.4.99'],
        'conflicts' => [],
        'suggests' => []
    ]
];
