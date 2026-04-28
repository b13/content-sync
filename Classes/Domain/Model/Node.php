<?php

declare(strict_types=1);

namespace B13\ContentSync\Domain\Model;

/*
 * This file is part of TYPO3 CMS-based extension "content-sync" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

class Node
{
    protected bool $local = false;
    protected string $connection = '';
    protected string $basePath = '';
    protected string $bin = '';

    public function fromArray(array $properties): Node
    {
        $this->local = (bool)$properties['local'];
        $this->connection = (string)$properties['connection'];
        $this->basePath = (string)$properties['basePath'];
        $this->bin = (string)$properties['bin'];
        return $this;
    }

    public function isLocal(): bool
    {
        return $this->local;
    }

    public function getConnection(): string
    {
        if ($this->connection === '' && $this->isLocal()) {
            return 'local';
        }
        return $this->connection;
    }

    public function getBasePath(): string
    {
        return rtrim($this->basePath, '/') . '/';
    }

    public function getBin(): string
    {
        return $this->bin;
    }
}
