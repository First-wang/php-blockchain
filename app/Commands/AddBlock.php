<?php

namespace App\Commands;

use App\Services\BlockChain;
use Illuminate\Console\Command;

class AddBlock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'addblock {data : 区块记录的数据}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '向区块链中添加一个区块';

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
        $data = $this->argument('data');
        $this->task('mining block:', function () use ($data) {
            $bc = BlockChain::NewBlockChain();
            $bc->addBlock($data);
            return true;
        });
    }
}
