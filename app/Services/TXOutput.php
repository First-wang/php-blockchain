<?php


namespace App\Services;


class TXOutput
{
    /**
     * @var int $value
     */
    public $value;

    /**
     * @var string $scriptPubKey
     */
    public $scriptPubKey;

    public function __construct(int $value, string $scriptPubKey)
    {
        $this->value = $value;
        $this->scriptPubKey = $scriptPubKey;
    }

    public function canBeUnlockedWith(string $unlockingData): bool
    {
        return $this->scriptPubKey == $unlockingData;
    }
}
