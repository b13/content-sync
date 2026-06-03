<?php

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$config->getFinder()->exclude(['var', 'public', 'Build'])->in(__DIR__ . '/..');
$config->addRules([
    'nullable_type_declaration' => [
        'syntax' => 'question_mark',
    ],
    'nullable_type_declaration_for_default_null_value' => true,
    'declare_strict_types' => true,
]);
return $config;
