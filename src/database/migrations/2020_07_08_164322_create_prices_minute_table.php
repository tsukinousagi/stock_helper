<?php
/**
 * 記錄個股每分鐘價量的table
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricesMinuteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prices_minute', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->dateTime('d_date');
            $table->float('price_open');
            $table->float('price_high');
            $table->float('price_low');
            $table->float('price_close');
            $table->float('volume');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['code', 'd_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prices_minute');
    }
}
