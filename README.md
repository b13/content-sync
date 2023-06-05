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

    content-sync:collect-garbage

checks for any outdated jobs that are still running or waiting due to an error and removes them from the queue.

Ensure to have your scheduler set up.

### Code Analysis

- run `./Build/Scripts/runTests.sh -p 8.1 -s composerUpdate`
- run `./Build/Scripts/runTests.sh -p 8.1 -t 12 -s phpstan`
- run `./Build/Scripts/runTests.sh -p 8.1 -t 12 -s cgl`


## License

As TYPO3 Core, _content_sync_ is licensed under GPL2 or later.
