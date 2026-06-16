<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_user_id_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('notification', function (Blueprint $table) {
            $table->dropIndex('idx_user_id_created_at');
        });
    }
};
