<?php

namespace App\Commands;

use App\Services\Wallets;
use Illuminate\Console\Command;

class ListAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listaddresses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '钱包所有地址';

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

        $addresses = $wallets->getAddresses();

        foreach ($addresses as $address) {



            $this->info($address);
        }
    }
}
