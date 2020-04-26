<?php

namespace App\Commands;

use App\Services\BlockChain;
use Illuminate\Console\Command;

class PrintChain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'printchain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '格式化打印出所有块信息';

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
        $bc = BlockChain::NewBlockChain();
        foreach ($bc as $block) {
            $this->info('-----------------');
            $this->info('   hash: ' . $block->hash);
            $this->info('   prev hash: ' . $block->prevBlockHash);
            $this->info('   timestamp: ' . $block->timestamp);
            $this->info('   data: ' . $block->data);
        }
    }
}
