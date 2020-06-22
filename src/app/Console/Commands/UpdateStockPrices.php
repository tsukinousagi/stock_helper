<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DailyService;

class UpdateStockPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updatestockprices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Stock Prices';

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
        // todo 接參數指定日期代號
        $obj_daily = new DailyService();
        $obj_daily->getTodayGoodsPrices();
        
    }
}
