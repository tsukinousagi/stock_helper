<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoodsPriceService;

class CheckPriceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkpricedata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '往前檢查前180個交易日的價量資料是否齊全';

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
        $obj_price = new GoodsPriceService();
        $ret = $obj_price->checkPriceData();
    }
}
