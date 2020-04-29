<?php


namespace App\Services;


use Illuminate\Support\Facades\Cache;

class UTXOSet
{
    /**
     * @var BlockChain $blockChain
     */
    public $blockChain;

    public function __construct(BlockChain $bc)
    {
        $this->blockChain = $bc;
    }

    public function reindex()
    {
        // 先清除缓存中所有数据
        Cache::store('utxoBucket')->clear();

        $UTXOs = $this->blockChain->findUTXO();

        foreach ($UTXOs as $txId => $outputs) {
            Cache::store('utxoBucket')->put($txId, serialize($outputs));
        }
        Cache::store('utxoBucket')->put('tx_ids', serialize(array_keys($UTXOs)));
    }

    public function findSpendableOutputs(string $pubKeyHash, int $amount): array
    {
        $accumulated = 0;
        $unspentOutputs = [];

        $txIdsStr = Cache::store('utxoBucket')->get('tx_ids');
        $txIds = unserialize($txIdsStr);

        foreach ($txIds as $txId) {
            /**
             * @var TXOutput[] $outputs
             */
            $outputs = unserialize(Cache::store('utxoBucket')->get($txId));

            foreach ($outputs as $outIdx => $txOutput) {
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

    public function findUTXO(string $pubKeyHash): array
    {
        $UTXOs = [];

        $txIdsStr = Cache::store('utxoBucket')->get('tx_ids');
        $txIds = unserialize($txIdsStr);

        foreach ($txIds as $txId) {
            /**
             * @var TXOutput[] $outputs
             */
            $outputs = unserialize(Cache::store('utxoBucket')->get($txId));

            foreach ($outputs as $outIdx => $txOutput) {
                if ($txOutput->isLockedWithKey($pubKeyHash)) {
                    $UTXOs[] = $txOutput;
                }
            }
        }
        return $UTXOs;
    }

    /**
     * 保持UTXO集处于最新状态
     * @param Block $block
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function update(Block $block)
    {
        foreach ($block->transactions as $tx) {
            $txId = $tx->id;

            $txIdsStr = Cache::store('utxoBucket')->get('tx_ids');
            $txIds = unserialize($txIdsStr);

            if (!$tx->isCoinbase()) {
                foreach ($tx->txInputs as $txInput) {
                    $spentTxId = $txInput->txId;
                    $spentTxVout = $txInput->vOut;

                    $updateOuts = unserialize(Cache::store('utxoBucket')->get($spentTxId));
                    unset($updateOuts[$spentTxVout]);

                    if (count($updateOuts) > 0) {
                        Cache::store('utxoBucket')->put($spentTxId, serialize($updateOuts));
                    } else {
                        Cache::store('utxoBucket')->forget($spentTxId);

                        $deleteKey = array_search($spentTxId, $txIds);
                        unset($txIds[$deleteKey]);
                    }
                }

            }

            $newOutputs = [];
            foreach ($tx->txOutputs as $txOutput) {
                $newOutputs[] = new TXOutput($txOutput->value, $txOutput->pubKeyHash);
            }

            $txIds[] = $tx->id;
            Cache::store('utxoBucket')->put('tx_ids', serialize($txIds));
            Cache::store('utxoBucket')->put($txId, serialize($newOutputs));
        }

    }

}
