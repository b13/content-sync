<?php

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$config->getFinder()->in(['Classes', 'Configuration']);
$config->getFinder()->exclude(['var', 'public']);
return $config;
