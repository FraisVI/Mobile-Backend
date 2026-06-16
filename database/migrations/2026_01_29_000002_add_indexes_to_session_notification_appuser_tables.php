<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session', function (Blueprint $table) {
            $table->index('user_id', 'idx_user_id');
            $table->index('fcm_token', 'idx_fcm_token');
            $table->index(['user_id', 'fcm_token'], 'idx_user_fcm');
            $table->index('phone', 'idx_phone');
            $table->index('lastused', 'idx_lastused');
        });

        Schema::table('notification', function (Blueprint $table) {
            $table->index('user_id', 'idx_user_id');
            $table->index('article_id', 'idx_article_id');
            $table->index('created_at', 'idx_created_at');
            $table->index('rate_hash', 'idx_rate_hash');
        });

        Schema::table('appuser', function (Blueprint $table) {
            $table->index('email', 'idx_email');
            $table->index('notify', 'idx_notify');
            $table->index('created_at', 'idx_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('session', function (Blueprint $table) {
            $table->dropIndex('idx_user_id');
            $table->dropIndex('idx_fcm_token');
            $table->dropIndex('idx_user_fcm');
            $table->dropIndex('idx_phone');
            $table->dropIndex('idx_lastused');
        });

        Schema::table('notification', function (Blueprint $table) {
            $table->dropIndex('idx_user_id');
            $table->dropIndex('idx_article_id');
            $table->dropIndex('idx_created_at');
            $table->dropIndex('idx_rate_hash');
        });

        Schema::table('appuser', function (Blueprint $table) {
            $table->dropIndex('idx_email');
            $table->dropIndex('idx_notify');
            $table->dropIndex('idx_created_at');
        });
    }
};
