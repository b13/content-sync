# EXT:content_sync

Sync Database Tables and Files between two TYPO3 Installations

## Requiremnts

* MySQL is used as DBMS and the mysqldump and mysql binaries  must be available in the Path
* SSH-Connection to Remote Node by Key Authentication
* typo3-console on target and source node must be available

## Extension Configuration

* Configure Database Tables and Files to sync
* Configure your Source- and Target Node (TYPO3 Instances)

## Backend Access

is granted to Admin Users and to Users with UserTS

    options.enableContentSync=1


## Commands

    content-sync:job:create
    
creates a new Job from Extension-Configuration

    content-sync:runner
    
runs one waiting Job

### Code Analyses

- run `composer install`
- run ``vendor/bin/phpstan analyse -c Resources/Private/Configuration/phpstan.neon``
- run ``vendor/bin/php-cs-fixer fix --config=vendor/typo3/coding-standards/templates/extension_php_cs.dist --dry-run --stop-on-violation --using-cache=no Classes``