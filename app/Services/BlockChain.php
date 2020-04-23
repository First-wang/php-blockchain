<?php


namespace App\Services;


class BlockChain
{
    /**
     * @var Block[] $blocks
     */
    public $blocks;

    public function __construct(Block $block)
    {
        $this->blocks[] = $block;
    }

    // 加入一个块到区块链中
    public function addBlock(string $data)
    {
        $prevBlock = $this->blocks[count($this->blocks) - 1];
        $newBlock = new Block($data, $prevBlock->hash);
        $this->blocks[] = $newBlock;
    }

    // 初始化创世区块
    public static function NewGenesisBlock()
    {
        $block = new Block('Genesis Block', '');

        return new BlockChain($block);
    }
}
