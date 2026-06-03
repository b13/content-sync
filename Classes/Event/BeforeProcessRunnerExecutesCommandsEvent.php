<?php

declare(strict_types=1);

namespace B13\ContentSync\Event;

/*
 * This file is part of TYPO3 CMS-based extension "content-sync" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\ContentSync\Domain\Model\Configuration;

final class BeforeProcessRunnerExecutesCommandsEvent
{
    public int $timeoutPerProcess = 60;

    public function __construct(
        public readonly Configuration $configuration,
        public array $commands
    ) {}
}
