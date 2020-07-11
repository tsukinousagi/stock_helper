<?php
/**
 * 操作Telegram Bot Webhook的指令
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramBotAPIService;

class SetTelegramBotWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:settelegramwebhook {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '開啟／關閉Telegram的Webhook';

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
        // 設定webhook開關
        $obj_telegram = new TelegramBotAPIService();
        $action = $this->argument('action');
        if (in_array($action, ['on', 'enable'])) {
            $action_text = '開啟';
            $ret = $obj_telegram->setWebhook();
        } else if (in_array($action, ['off', 'disable'])) {
            $action_text = '關閉';
            $ret = $obj_telegram->deleteWebhook();
        }

        // 顯示訊息
        if ($ret) {
            $msg = $action_text . '成功';
        } else {
            $msg = $action_text . '失敗';
        }
        
        echo($msg . PHP_EOL);
    }
}
