<?php


namespace App\Services;


class TXInput
{
    /**
     * @var string $txId
     */
    public $txId;

    /**
     * @var int $vOut
     */
    public $vOut;

    /**
     * @var string $scriptSig
     */
    public $scriptSig;

    public function __construct(string $txId, int $vOut, string $scriptSig)
    {
        $this->txId = $txId;
        $this->vOut = $vOut;
        $this->scriptSig = $scriptSig;
    }

    public function canUnlockOutputWith(string $unlockingData): bool
    {
        return $this->scriptSig == $unlockingData;
    }
}
