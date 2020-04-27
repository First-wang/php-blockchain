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
     * @param Transaction[] $transactions
     * @throws \Exception
     */
    public function mineBlock(array $transactions)
    {
        $lastHash = Cache::get('l');
        if (is_null($lastHash)) {
            echo "还没有区块链，请先初始化";
            exit;
        }

        foreach ($transactions as $tx) {
            if (!$this->verifyTransaction($tx)) {
                echo "交易验证失败";
                exit(0);
            }
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

    public static function GetBlockChain(): BlockChain
    {
        if (!Cache::has('l')) {
            echo "还没有区块链，请先初始化";
            exit;
        }

        return new BlockChain(Cache::get('l'));
    }

    /**
     * 找出地址的未花费交易
     * @param string $pubKeyHash
     * @return Transaction[]
     * @throws \Exception
     */
    public function findUnspentTransactions(string $pubKeyHash): array
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

                    if ($txOutput->isLockedWithKey($pubKeyHash)) {
                        $unspentTXs[$txId] = $tx;
                    }
                }

                if (!$tx->isCoinbase()) {
                    foreach ($tx->txInputs as $txInput) {
                        if ($txInput->usesKey($pubKeyHash)) {
                            $spentTXOs[$txInput->txId][] = $txInput->vOut;
                        }
                    }
                }
            }
        }
        return $unspentTXs;
    }

    /**
     * @param string $pubKeyHash
     * @return array
     * @throws \Exception
     */
    public function findSpentOutputs(string $pubKeyHash): array
    {
        $spentTXOs = [];
        /**
         * @var Block $block
         */
        foreach ($this as $block) {
            foreach ($block->transactions as $tx) {
                if (!$tx->isCoinbase()) {
                    foreach ($tx->txInputs as $txInput) {
                        if ($txInput->usesKey($pubKeyHash)) {
                            $spentTXOs[$txInput->txId][] = $txInput->vOut;
                        }
                    }
                }
            }
        }
        return $spentTXOs;
    }

    /**
     * @param string $pubKeyHash
     * @param int $amount
     * @return array
     * @throws \Exception
     */
    public function findSpendableOutputs(string $pubKeyHash, int $amount): array
    {
        $unspentOutputs = [];
        $unspentTXs = $this->findUnspentTransactions($pubKeyHash);
        $spentTXOs = $this->findSpentOutputs($pubKeyHash);
        $accumulated = 0;

        /**
         * @var Transaction $tx
         */
        foreach ($unspentTXs as $tx) {
            $txId = $tx->id;

            foreach ($tx->txOutputs as $outIdx => $txOutput) {
                if (isset($spentTXOs[$txId])) {
                    foreach ($spentTXOs[$txId] as $spentOutIdx) {
                        if ($spentOutIdx == $outIdx) {
                            // 说明这个tx的这个outIdx被花费过
                            continue 2;
                        }
                    }
                }

                if ($txOutput->isLockedWithKey($pubKeyHash) && $accumulated < $amount) {
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
     * @param string $pubKeyHash
     * @return TXOutput[]
     * @throws \Exception
     */
    public function findUTXO(string $pubKeyHash): array
    {
        $UTXOs = [];
        $unspentTXs = $this->findUnspentTransactions($pubKeyHash);
        $spentTXOs = $this->findSpentOutputs($pubKeyHash);

        foreach ($unspentTXs as $transaction) {
            $txId = $transaction->id;
            foreach ($transaction->txOutputs as $outIdx => $output) {
                if (isset($spentTXOs[$txId])) {
                    foreach ($spentTXOs[$txId] as $spentOutIdx) {
                        if ($spentOutIdx == $outIdx) {
                            // 说明这个tx的这个outIdx被花费过
                            continue 2;
                        }
                    }
                }

                if ($output->isLockedWithKey($pubKeyHash)) {
                    $UTXOs[] = $output;
                }
            }
        }
        return $UTXOs;
    }

    /**
     * @param Transaction $tx
     * @param string $privateKey
     * @throws \Exception
     */
    public function signTransaction(Transaction $tx, string $privateKey)
    {
        $prevTXs = [];
        foreach ($tx->txInputs as $txInput) {
            $prevTx = $this->findTransaction($txInput->txId);
            $prevTXs[$prevTx->id] = $prevTx;
        }
        $tx->sign($privateKey, $prevTXs);
    }

    /**
     * @param Transaction $tx
     * @return bool
     * @throws \Exception
     */
    public function verifyTransaction(Transaction $tx): bool
    {
        $prevTXs = [];
        foreach ($tx->txInputs as $txInput) {
            $prevTx = $this->findTransaction($txInput->txId);
            $prevTXs[$prevTx->id] = $prevTx;
        }
        return $tx->verify($prevTXs);
    }

    /**
     * @param string $txId
     * @return Transaction
     */
    public function findTransaction(string $txId): Transaction
    {
        /**
         * @var Block $block
         */
        foreach ($this as $block) {
            foreach ($block->transactions as $tx) {
                if ($tx->id == $txId) {
                    return $tx;
                }
            }
        }
        echo "Transaction is not found";
        exit(0);
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
