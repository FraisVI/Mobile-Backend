<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ip_phone', function (Blueprint $table) {
            $table->id();
            $table->string('ip');
            $table->string('phone');
            $table->timestamp('last_request');
            $table->integer('attempts')->default(0);
            $table->timestamp('hour_ban')->nullable();
            $table->timestamp('second_hour_ban')->nullable();
            $table->foreign('ip')->references('ip')->on('ips')->onDelete('cascade');
            $table->foreign('phone')->references('phone')->on('phones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ip_phone');
    }
};
