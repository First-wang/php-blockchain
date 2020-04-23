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

    /**
     * @var int $nonce
     */
    public $nonce;

    public function __construct(string $data, string $prevBlockHash)
    {
        $this->prevBlockHash = $prevBlockHash;
        $this->data = $data;
        $this->timestamp = time();

        $pow = new ProofOfWork($this);
        list($nonce, $hash) = $pow->run();

        $this->nonce = $nonce;
        $this->hash = $hash;
    }

}
