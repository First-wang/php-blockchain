<?php


namespace App\Services;


use Illuminate\Support\Facades\Cache;

class BlockChain implements \Iterator
{
    /**
     * // 存放最后一个块的hash
     * @var string $tips
     */
    public $tips;

    /**
     * 迭代器指向的当前块Hash
     * @var string $iteratorHash
     */
    private $iteratorHash;

    /**
     * 迭代器指向的当前块Hash
     * @var Block $iteratorBlock
     */
    private $iteratorBlock;

    public function __construct(string $tips)
    {
        $this->tips = $tips;
    }

    // 加入一个块到区块链中
    public function addBlock(string $data)
    {
        // 获取最后一个块
        $prevBlock = unserialize(Cache::get($this->tips));

        $newBlock = new Block($data, $prevBlock->hash);

        // 存入最后一个块到数据库，并更新 l 和 tips
        Cache::put($newBlock->hash, serialize($newBlock));
        Cache::put('l', $newBlock->hash);
        $this->tips = $newBlock->hash;
    }

    // 新建区块链
    public static function NewBlockChain(): BlockChain
    {
        if (Cache::has('l')) {
            // 存在区块链
            $tips = Cache::get('l');
        } else {
            $genesis = Block::NewGenesisBlock();

            Cache::put($genesis->hash, serialize($genesis));

            Cache::put('l', $genesis->hash);

            $tips = $genesis->hash;
        }
        return new BlockChain($tips);
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->iteratorBlock = unserialize(Cache::get($this->iteratorHash));
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        return $this->iteratorHash = $this->iteratorBlock->prevBlockHash;
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->iteratorHash;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->iteratorHash != '';
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->iteratorHash = $this->tips;
    }
}
