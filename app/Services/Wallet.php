<?php


namespace App\Services;


use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Key\Factory\PublicKeyFactory;
use BitWasp\Bitcoin\Key\KeyToScript\Factory\P2pkhScriptDataFactory;

class Wallet
{
    /**
     * @var string $privateKey
     */
    public $privateKey;

    /**
     * @var string $publicKey
     */
    public $publicKey;

    /**
     * Wallet constructor.
     * @throws \BitWasp\Bitcoin\Exceptions\RandomBytesFailure
     */
    public function __construct()
    {
        list($privateKey, $publicKey) = $this->newKeyPair();
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getAddress(): string
    {
        $addrCreator = new AddressCreator();
        $factory = new P2pkhScriptDataFactory();

        $scriptPubKey = $factory->convertKey((new PublicKeyFactory())->fromHex($this->publicKey))->getScriptPubKey();
        $address = $addrCreator->fromOutputScript($scriptPubKey);

        return $address->getAddress(Bitcoin::getNetwork());
    }

    public function getPubKeyHash(): string
    {
        $pubKeyIns = (new PublicKeyFactory())->fromHex($this->publicKey);
        return $pubKeyIns->getPubKeyHash()->getHex();
    }

    /**
     * @return array
     * @throws \BitWasp\Bitcoin\Exceptions\RandomBytesFailure
     */
    private function newKeyPair(): array
    {
        $privateKeyFactory = new PrivateKeyFactory();
        $privateKey = $privateKeyFactory->generateCompressed(new Random());
        $publicKey = $privateKey->getPublicKey();
        return [$privateKey->getHex(), $publicKey->getHex()];
    }
}
