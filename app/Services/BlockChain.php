<?php


namespace App\Services;


use Illuminate\Support\Facades\Cache;

class BlockChain implements \Iterator
{
    const genesisCoinbaseData = 'The Times 03/Jan/2009 Chancellor on brink of second bailout for banks';

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

    /**
     * @param array $transactions
     * @throws \Exception
     */
    public function mineBlock(array $transactions)
    {
        $lastHash = Cache::get('l');
        if (is_null($lastHash)) {
            throw new \Exception('还没有区块链，请先初始化');
        }

        $block = new Block($transactions, $lastHash);

        $this->tips = $block->hash;
        Cache::put('l', $block->hash);
        Cache::put($block->hash, serialize($block));
    }

    // 新建区块链
    public static function NewBlockChain(string $address): BlockChain
    {
        if (Cache::has('l')) {
            // 存在区块链
            $tips = Cache::get('l');
        } else {
            $coinbase = Transaction::NewCoinbaseTX($address, self::genesisCoinbaseData);

            $genesis = Block::NewGenesisBlock($coinbase);

            Cache::put($genesis->hash, serialize($genesis));

            Cache::put('l', $genesis->hash);

            $tips = $genesis->hash;
        }
        return new BlockChain($tips);
    }

    /**
     * @throws \Exception
     */
    public static function GetBlockChain(): BlockChain
    {
        if (!Cache::has('l')) {
            throw new \Exception('还没有区块链，请先初始化');
        }

        return new BlockChain(Cache::get('l'));
    }

    /**
     * 找出地址的未花费交易
     * @param string $address
     * @return Transaction[]
     */
    public function findUnspentTransactions(string $address): array
    {
        $unspentTXs = [];
        $spentTXOs = [];

        /**
         * @var Block $block
         */
        foreach ($this as $block) {

            foreach ($block->transactions as $tx) {
                $txId = $tx->id;

                foreach ($tx->txOutputs as $outIdx => $txOutput) {
                    if (isset($spentTXOs[$txId])) {
                        foreach ($spentTXOs[$txId] as $spentOutIdx) {
                            if ($spentOutIdx == $outIdx) {
                                continue 2;
                            }
                        }
                    }

                    if ($txOutput->canBeUnlockedWith($address)) {
                        $unspentTXs[$txId] = $tx;
                    }
                }

                if (!$tx->isCoinbase()) {
                    foreach ($tx->txInputs as $txInput) {
                        if ($txInput->canUnlockOutputWith($address)) {
                            $spentTXOs[$txInput->txId][] = $txInput->vOut;
                        }
                    }
                }
            }
        }
        return $unspentTXs;
    }

    public function findSpendableOutputs(string $address, int $amount): array
    {
        $unspentOutputs = [];
        $unspentTXs = $this->findUnspentTransactions($address);
        $accumulated = 0;

        /**
         * @var Transaction $tx
         */
        foreach ($unspentTXs as $tx) {
            $txId = $tx->id;

            foreach ($tx->txOutputs as $outIdx => $txOutput) {
                if ($txOutput->canBeUnlockedWith($address) && $accumulated < $amount) {
                    $accumulated += $txOutput->value;
                    $unspentOutputs[$txId][] = $outIdx;
                    if ($accumulated >= $amount) {
                        break 2;
                    }
                }
            }
        }
        return [$accumulated, $unspentOutputs];
    }

    /**
     * @param string $address
     * @return TXOutput[]
     */
    public function findUTXO(string $address): array
    {
        $UTXOs = [];
        $unspentTXs = $this->findUnspentTransactions($address);

        foreach ($unspentTXs as $transaction) {
            foreach ($transaction->txOutputs as $output) {
                if ($output->canBeUnlockedWith($address)) {
                    $UTXOs[] = $output;
                }
            }
        }
        return $UTXOs;
    }

    /**
     * @param string $address
     * @return TXOutput[]
     */
//    public function findAllUTXO(string $address): array
//    {
//        $spentTXOs = [];
//        $UTXOs = [];
//
//        /**
//         * @var Block $block
//         */
//        foreach ($this as $block) {
//            foreach ($block->transactions as $tx) {
//                $txId = $tx->id;
//
//                foreach ($tx->txOutputs as $outIdx => $txOutput) {
//                    if (isset($spentTXOs[$txId])) {
//                        foreach ($spentTXOs[$txId] as $spentOutIdx) {
//                            if ($spentOutIdx == $outIdx) {
//                                continue 2;
//                            }
//                        }
//                    }
//
//                    if ($txOutput->canBeUnlockedWith($address)) {
//                        $UTXOs[] = $txOutput;
//                    }
//                }
//
//                if (!$tx->isCoinbase()) {
//                    foreach ($tx->txInputs as $txInput) {
//                        if ($txInput->canUnlockOutputWith($address)) {
//                            $spentTXOs[$txInput->txId][] = $txInput->vOut;
//                        }
//                    }
//                }
//            }
//        }
//        return $UTXOs;
//    }

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
