<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoodsPriceService;

class CheckPriceDataByGoods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkpricedatabygoods {good}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '指定單一個股，檢查價量資料';

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
        // 處理參數
        $good = $this->argument('good');

        $obj_price = new GoodsPriceService();
        $ret = $obj_price->checkPriceDataByGood($good);
    }
}
