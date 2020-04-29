<?php

namespace App\Commands;

use App\Services\BlockChain;
use App\Services\UTXOSet;
use App\Services\Wallets;
use Illuminate\Console\Command;

class Balance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getbalance {address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '查询给定地址余额';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $address = $this->argument('address');

        $bc = BlockChain::GetBlockChain();
        $utxoSet = new UTXOSet($bc);

        $wallets = new Wallets();
        $wallet = $wallets->getWallet($address);

        $UTXOs = $utxoSet->findUTXO($wallet->getPubKeyHash());

        $balance = 0;
        foreach ($UTXOs as $output) {
            $balance += $output->value;
        }
        $this->info(sprintf("balance of address '%s' is: %s", $address, $balance));
    }
}
