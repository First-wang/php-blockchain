<?php

namespace App\Commands;

use App\Services\Wallets;
use Illuminate\Console\Command;

class CreateWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'createwallet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建一个钱包';

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
     */
    public function handle()
    {
        $wallets = new Wallets();

        $address = $wallets->createWallet();
        $wallets->saveToFile();

        $this->info("Your new address: {$address}");
    }
}
