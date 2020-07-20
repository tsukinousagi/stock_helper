<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoodsRealtimePriceService;

class GetRealtimePriceByMinute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:getrealtimepricebyminute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每分鐘執行，盤中取得即時價量';

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
        //
        $obj_realtime = new GoodsRealtimePriceService();
        $ret = $obj_realtime->getGoodsRealtimeData();
//        echo($ret);
    }
}
