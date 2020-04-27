<?php


namespace App\Services;


use BitWasp\Bitcoin\Key\Factory\PublicKeyFactory;

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
     * @var string $signature
     */
    public $signature;

    /**
     * @var string $pubKey
     */
    public $pubKey;

    public function __construct(string $txId, int $vOut, string $signature, string $pubKey)
    {
        $this->txId = $txId;
        $this->vOut = $vOut;
        $this->signature = $signature;
        $this->pubKey = $pubKey;
    }

    /**
     * @param string $pubKeyHash
     * @return bool
     * @throws \Exception
     */
    public function usesKey(string $pubKeyHash): bool
    {
        $pubKeyIns = (new PublicKeyFactory())->fromHex($this->pubKey);
        return $pubKeyIns->getPubKeyHash()->getHex() == $pubKeyHash;
    }
}
