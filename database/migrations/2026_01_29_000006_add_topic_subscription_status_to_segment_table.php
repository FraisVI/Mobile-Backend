<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('segment', function (Blueprint $table) {
            $table->string('topic_subscription_status', 20)->nullable()->after('firebase_topic')
                ->comment('pending, in_progress, completed, failed');
        });
    }

    public function down(): void
    {
        Schema::table('segment', function (Blueprint $table) {
            $table->dropColumn('topic_subscription_status');
        });
    }
};
