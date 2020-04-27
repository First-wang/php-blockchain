<?php


namespace App\Services;



use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Key\Factory\PublicKeyFactory;
use BitWasp\Bitcoin\Signature\SignatureFactory;
use BitWasp\Buffertools\Buffer;

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

        $txIn = new TXInput('', -1, '', $data);
        $txOut = TXOutput::NewTxOutput(self::subsidy, $to);
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
        $wallets = new Wallets();
        $wallet = $wallets->getWallet($from);

        list($acc, $validOutputs) = $bc->findSpendableOutputs($wallet->getPubKeyHash(), $amount);
        if ($acc < $amount) {
            echo "余额不足";
            exit;
        }

        $inputs = [];
        $outputs = [];

        /**
         * @var TXOutput $output
         */
        foreach ($validOutputs as $txId => $outsIdx) {
            foreach ($outsIdx as $outIdx) {
                $inputs[] = new TXInput($txId, $outIdx, '', $wallet->publicKey);
            }
        }

        $outputs[] = TXOutput::NewTxOutput($amount, $to);
        if ($acc > $amount) {
            $outputs[] = TXOutput::NewTxOutput($acc - $amount, $from);
        }
        $tx = new Transaction($inputs, $outputs);
        $bc->signTransaction($tx, $wallet->privateKey);
        return $tx;
    }

    public function isCoinbase(): bool
    {
        return (count($this->txInputs) == 1) && ($this->txInputs[0]->txId == '') && ($this->txInputs[0]->vOut == -1);
    }

    /**
     * @param string $privateKey
     * @param Transaction[] $prevTXs
     * @throws \Exception
     */
    public function sign(string $privateKey, array $prevTXs)
    {
        if ($this->isCoinbase()) {
            return;
        }

        $txCopy = $this->trimmedCopy();

        foreach ($txCopy->txInputs as $inId => $txInput) {
            $prevTx = $prevTXs[$txInput->txId];
            $txCopy->txInputs[$inId]->signature = '';
            $txCopy->txInputs[$inId]->pubKey = $prevTx->txOutputs[$txInput->vOut]->pubKeyHash;
            $txCopy->setId();
            $txCopy->txInputs[$inId]->pubKey = '';

            $signature = (new PrivateKeyFactory())->fromHexCompressed($privateKey)->sign(new Buffer($txCopy->id))->getHex();
            $this->txInputs[$inId]->signature = $signature;
        }
    }

    /**
     * @param array $prevTXs
     * @return bool
     * @throws \Exception
     */
    public function verify(array $prevTXs): bool
    {
        $txCopy = $this->trimmedCopy();

        foreach ($this->txInputs as $inId => $txInput) {
            $prevTx = $prevTXs[$txInput->txId];
            $txCopy->txInputs[$inId]->signature = '';
            $txCopy->txInputs[$inId]->pubKey = $prevTx->txOutputs[$txInput->vOut]->pubKeyHash;
            $txCopy->setId();
            $txCopy->txInputs[$inId]->pubKey = '';

            $signature = $txInput->signature;
            $signatureInstance = SignatureFactory::fromHex($signature);

            $pubKey = $txInput->pubKey;
            $pubKeyInstance = (new PublicKeyFactory())->fromHex($pubKey);

            $bool = $pubKeyInstance->verify(new Buffer($txCopy->id), $signatureInstance);
            if ($bool == false) {
                return false;
            }
        }
        return true;
    }

    private function trimmedCopy(): Transaction
    {
        $inputs = [];
        $outputs = [];

        foreach ($this->txInputs as $txInput) {
            $inputs[] = new TXInput($txInput->txId, $txInput->vOut, '', '');
        }

        foreach ($this->txOutputs as $txOutput) {
            $outputs[] = new TXOutput($txOutput->value, $txOutput->pubKeyHash);
        }

        return new Transaction($inputs, $outputs);
    }

    private function setId()
    {
        $this->id = hash('sha256', serialize($this));
    }
}
