<?php


namespace App\Services;


use GMP;

class ProofOfWork
{
    /**
     * @var Block $block
     */
    public $block;

    /**
     * @var GMP $target
     */
    public $target;

    public function __construct(Block $block)
    {
        $targetBits = config('blockchain.targetBits');

        $this->target = gmp_pow('2', (256 - $targetBits));

        $this->block = $block;
    }

    public function prepareData(int $nonce): string
    {
        return implode('', [
            $this->block->prevBlockHash,
            $this->block->hashTransactions(),
            $this->block->timestamp,
            config('blockchain.targetBits'),
            $nonce
        ]);
    }

    public function run(): array
    {
        $nonce = 0;
        $hash = '';
        while (true) {
            $data = $this->prepareData($nonce);
            $hash = hash('sha256', $data);
            if (gmp_cmp('0x' . $hash, $this->target) == -1) {
                break;
            }
            $nonce++;
        }
        return [$nonce, $hash];
    }
}
