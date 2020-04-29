<?php

namespace App\Services;


class Block
{
    /**
     * @var int $timestamp
     */
    public $timestamp;

    /**
     * @var Transaction[] $transactions
     */
    public $transactions;

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

    public function __construct(array $transactions, string $prevBlockHash)
    {
        $this->prevBlockHash = $prevBlockHash;
        $this->transactions = $transactions;
        $this->timestamp = time();

        $pow = new ProofOfWork($this);
        list($nonce, $hash) = $pow->run();

        $this->nonce = $nonce;
        $this->hash = $hash;
    }

    public static function NewGenesisBlock(Transaction $coinbase)
    {
        return $block = new Block([$coinbase], '');
    }

    public function hashTransactions(): string
    {
        $txsHashArr = [];
        foreach ($this->transactions as $transaction) {
            $txsHashArr[] = $transaction->id;
        }

        $mTree = new MerkleTree($txsHashArr);
        return $mTree->rootNode->data;
    }
}
