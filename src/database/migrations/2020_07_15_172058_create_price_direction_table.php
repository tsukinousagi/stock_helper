<?php
/**
 * 記錄個股同一交易日內漲跌變化的table
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriceDirectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_direction', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->datetime('d_date'); // 有效交易日
            $table->float('direction'); // 漲跌情況
            $table->datetime('last_changed'); // 上次漲跌變化的時間
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_direction');
    }
}
