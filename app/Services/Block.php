<?php

namespace App\Services;

use Carbon\Carbon;

class Block
{
    /**
     * @var int $timestamp
     */
    public $timestamp;

    /**
     * @var string $data
     */
    public $data;

    /**
     * @var string $prevBlockHash
     */
    public $prevBlockHash;

    /**
     * @var string $hash
     */
    public $hash;

    public function __construct(string $data, string $prevBlockHash)
    {
        $this->prevBlockHash = $prevBlockHash;
        $this->data = $data;
        $this->timestamp = time();
        $this->hash = $this->setHash();
    }

    public function setHash(): string
    {
        return hash('sha256', implode('', [$this->timestamp, $this->prevBlockHash, $this->data]));
    }
}
