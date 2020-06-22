<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StockGoodsService;

class UpdateStockGoods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updatestockgoods';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新個股清單';

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
        $obj_stock_goods = new StockGoodsService();
        $ret = $obj_stock_goods->updateStockGoods();
    }
}
