<?php


namespace App\Services;


use BitWasp\Bitcoin\Address\AddressCreator;

class TXOutput
{
    /**
     * @var int $value
     */
    public $value;

    /**
     * @var string $pubKeyHash
     */
    public $pubKeyHash;

    public function __construct(int $value, string $pubKeyHash)
    {
        $this->value = $value;
        $this->pubKeyHash = $pubKeyHash;
    }

    public function isLockedWithKey(string $pubKeyHash): bool
    {
        return $this->pubKeyHash == $pubKeyHash;
    }

    public static function NewTxOutput(int $value, string $address)
    {
        $txOut = new TXOutput($value, '');
        $pubKeyHash = $txOut->lock($address);
        $txOut->pubKeyHash = $pubKeyHash;
        return $txOut;
    }

    private function lock(string $address): string
    {
        $addCreator = new AddressCreator();
        $addInstance = $addCreator->fromString($address);

        $pubKeyHash = $addInstance->getScriptPubKey()->getHex();    // 这是携带版本+后缀校验的值，需要裁剪一下
        return $pubKeyHash = substr($pubKeyHash, 6, mb_strlen($pubKeyHash) - 10);
    }
}
