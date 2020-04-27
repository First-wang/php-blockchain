<?php


namespace App\Services;


class Wallets
{
    /**
     * @var Wallet[] $wallets
     */
    public $wallets;

    public function __construct()
    {
        $this->loadFromFile();
    }

    public function createWallet(): string
    {
        $wallet = new Wallet();

        $address = $wallet->getAddress();

        $this->wallets[$address] = $wallet;

        return $address;
    }

    public function saveToFile()
    {
        $walletsSer = serialize($this->wallets);

        file_put_contents(storage_path() . '/walletFile', $walletsSer);
    }

    public function loadFromFile()
    {
        $wallets = [];
        if (file_exists(storage_path() . '/walletFile')) {
            $contents = file_get_contents(storage_path() . '/walletFile');

            if (!empty($contents)) {
                $wallets = unserialize($contents);
            }
        }
        $this->wallets = $wallets;
    }

    public function getWallet(string $from)
    {
        if (isset($this->wallets[$from])) {
            return $this->wallets[$from];
        }
        echo "钱包不存在该地址";
        exit(0);
    }

    public function getAddresses(): array
    {
        return array_keys($this->wallets);
    }
}
