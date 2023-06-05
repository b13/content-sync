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

    /**
     * @return bool
     */
    public function isLocal(): bool
    {
        return $this->local;
    }

    /**
     * @return string
     */
    public function getConnection(): string
    {
        if ($this->connection === '' && $this->isLocal()) {
            return 'local';
        }
        return $this->connection;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return rtrim($this->basePath, '/') . '/';
    }

    /**
     * @return string
     */
    public function getBin(): string
    {
        return $this->bin;
    }
}
