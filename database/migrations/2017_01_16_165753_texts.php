<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Texts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('texts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone');
            $table->string('message');
            $table->string('message_id');
            $table->string('sent_to');
            $table->string('secret');
            $table->string('device_id');
            $table->string('sent_timestamp');

            $table->timestamps();
        });
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()

    {
        Schema::dropIfExists('texts');
        //
    }
}
