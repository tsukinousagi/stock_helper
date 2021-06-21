<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PlurkPostService;

class PlurkPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:plurkpost {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get finance data, process them and make a plurk.';

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
        $type = $this->argument('type');
        $obj_plurk_post = new PlurkPostService();
        $obj_plurk_post->makePlurk($type);
    }
}
