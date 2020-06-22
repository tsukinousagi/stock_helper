<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DailyService;

class FixStockPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:fixstockprices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修正台股即時資料取不出資料的個股';

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
        $a = new DailyService();
        return $a->fixGoodsPrices();
    }
}
