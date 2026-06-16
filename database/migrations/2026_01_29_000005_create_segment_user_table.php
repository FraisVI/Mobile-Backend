<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('segment_user', function (Blueprint $table) {
            $table->unsignedInteger('segment_id');
            $table->integer('user_id');
            $table->timestamp('created_at')->nullable();
            $table->primary(['segment_id', 'user_id']);
            $table->index('user_id');
            $table->index('segment_id');
            $table->foreign('segment_id')->references('id')->on('segment')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('appuser')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('segment_user');
    }
};
