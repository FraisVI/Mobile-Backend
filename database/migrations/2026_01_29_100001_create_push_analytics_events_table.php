<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_analytics_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('send_id')->comment('ID записи в notification_sent_log');
            $table->unsignedBigInteger('user_id')->comment('ID пользователя appuser');
            $table->enum('event_type', ['delivered', 'opened', 'click'])->comment('delivered=доставлено, opened=открыто, click=переход');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['send_id', 'user_id', 'event_type'], 'uq_send_user_event');
            $table->index('send_id', 'idx_send_id');
            $table->index(['send_id', 'event_type'], 'idx_send_event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_analytics_events');
    }
};
