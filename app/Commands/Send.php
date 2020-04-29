<?php

namespace App\Commands;

use App\Services\BlockChain;
use App\Services\Transaction;
use App\Services\UTXOSet;
use Illuminate\Console\Command;

class Send extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send {from : 发送地址} {to : 接收地址} {amount : 发送金额}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发送比特币给某人';

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
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Exception
     */
    public function handle()
    {
        $arguments = $this->arguments();

        $from = $arguments['from'];
        $to = $arguments['to'];
        $amount = $arguments['amount'];

        $this->task('send tx', function () use ($from, $to, $amount) {
            $bc = BlockChain::GetBlockChain();
            $utxoSet = new UTXOSet($bc);

            $tx = Transaction::NewUTXOTransaction($from, $to, $amount,$utxoSet);
            $coinbaseTx = Transaction::NewCoinbaseTX($from, '');

            $block = $bc->mineBlock([$coinbaseTx, $tx]);
            $utxoSet->update($block);

            return true;
        });

        $this->info('send success');
    }
}
