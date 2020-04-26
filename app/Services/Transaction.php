<?php


namespace App\Services;


class Transaction
{
    const subsidy = 50;

    /**
     * 当前交易的Hash
     * @var string $id
     */
    public $id;

    /**
     * @var TXInput[] $txInputs
     */
    public $txInputs;

    /**
     * @var TXOutput[] $txOutputs
     */
    public $txOutputs;

    public function __construct(array $txInputs, array $txOutputs)
    {
        $this->txInputs = $txInputs;
        $this->txOutputs = $txOutputs;
        $this->setId();
    }

    public static function NewCoinbaseTX(string $to, string $data): Transaction
    {
        if ($data == '') {
            $data = sprintf("Reward to '%s'", $to);
        }

        $txIn = new TXInput('', -1, $data);
        $txOut = new TXOutput(self::subsidy, $to);
        return new Transaction([$txIn], [$txOut]);
    }

    /**
     * @param string $from
     * @param string $to
     * @param int $amount
     * @param BlockChain $bc
     * @return Transaction
     * @throws \Exception
     */
    public static function NewUTXOTransaction(string $from, string $to, int $amount, BlockChain $bc): Transaction
    {
        list($acc, $validOutputs) = $bc->findSpendableOutputs($from, $amount);
        if ($acc < $amount) {
            throw new \Exception('余额不足');
        }

        $inputs = [];
        $outputs = [];

        /**
         * @var TXOutput $output
         */
        foreach ($validOutputs as $txId => $outsIdx) {
            foreach ($outsIdx as $outIdx) {
                $inputs[] = new TXInput($txId, $outIdx, $from);
            }
        }

        $outputs[] = new TXOutput($amount, $to);
        if ($acc > $amount) {
            $outputs[] = new TXOutput($acc - $amount, $from);
        }

        return new Transaction($inputs, $outputs);
    }

    public function isCoinbase(): bool
    {
        return (count($this->txInputs) == 1) && ($this->txInputs[0]->txId == '') && ($this->txInputs[0]->vOut == -1);
    }

    private function setId()
    {
        $this->id = hash('sha256', serialize($this));
    }
}
