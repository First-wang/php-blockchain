<?php

namespace App\Commands;

use App\Services\BlockChain;
use Illuminate\Console\Command;

class InitBlockChain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init-blockchain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化一个区块链，如果没有则创建';

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
        $this->task('init blockchain', function () {
            BlockChain::NewBlockChain();
            return true;
        });
    }
}
