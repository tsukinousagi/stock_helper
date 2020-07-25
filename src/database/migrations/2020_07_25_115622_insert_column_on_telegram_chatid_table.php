<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertColumnOnTelegramChatidTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('telegram_chatid', function($table) {
            $table->string('firstname')->after('username')->nullable(true);
            $table->string('lastname')->after('firstname')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('telegram_chatid', function($table) {
            $table->dropColumn('lastname');
            $table->dropColumn('firstname');
        });
    }
}
