# TYPO3 Extension "content_sync"

This extension syncs raw database tables and selected files of directories between two TYPO3 installations.

This can be used to sync between a "content master" system, where editors work, and a "production" system which does not have a TYPO3 backend available. Both systems are connected via SSH.

This extension is - by design - very simplistic, and for other use-cases we recommend using Workspaces for publishing processes or in2publish as a more sophisticated solution.

## Requirements

* MySQL is used as RDBMS and the mysqldump and mysql binaries must be available in the $PATH for PHP
* A SSH Connection to Remote Node by Key Authentication (can be configured via .ssh/config)
* helhum/typo3_console on target and source node must be available

## Extension Configuration

* Configure Database Tables and Files to sync
* Configure your Source- and Target Node (TYPO3 Instances)

## Backend Access

In order to see and kickstart a content sync, a new toolbar item on top is shown. Access is granted to Admin Users and to non-administrators with UserTSconfig:

    options.enableContentSync = 1


## Available TYPO3 CLI Commands

    content-sync:job:create
    
creates a new Job from Extension-Configuration

    content-sync:runner
    
runs one waiting job.

Ensure to have your scheduler set up.

### Code Analysis

- run `composer install`
- run ``vendor/bin/phpstan analyse -c Resources/Private/Configuration/phpstan.neon``
- run ``vendor/bin/php-cs-fixer fix --config=vendor/typo3/coding-standards/templates/extension_php_cs.dist --dry-run --stop-on-violation --using-cache=no Classes``


## License

As TYPO3 Core, _content_sync_ is licensed under GPL2 or later.
