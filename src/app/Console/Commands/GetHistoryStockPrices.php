<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoodsHistoryPriceService;

class GetHistoryStockPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:gethistorystockprices {year} {month}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '取得歷史股價';

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
        $d_year = $this->argument('year');
        $d_month = $this->argument('month');
        
        // 取得資料並寫入DB
        $obj_price = new GoodsHistoryPriceService();
        return $obj_price->getGoodsHistoryPriceByMonth($d_year, $d_month);
    }
}
