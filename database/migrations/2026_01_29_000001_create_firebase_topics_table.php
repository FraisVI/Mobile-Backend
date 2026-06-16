<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('segment', function (Blueprint $table) {
            $table->string('firebase_topic', 255)->nullable()->after('card_ids')->comment('Название Firebase топика для этого сегмента');
            $table->unique('firebase_topic', 'idx_firebase_topic');
        });

        Schema::create('firebase_topics', function (Blueprint $table) {
            $table->id();
            $table->string('topic_name', 255)->unique()->comment('Название топика в Firebase (только a-zA-Z0-9_-)');
            $table->string('description', 512)->nullable()->comment('Описание топика');
            $table->unsignedInteger('segment_id')->nullable()->comment('ID сегмента, связанного с топиком');
            $table->boolean('is_active')->default(true)->comment('Активен ли топик');
            $table->timestamps();

            $table->index('segment_id', 'idx_segment_id');
            $table->index('is_active', 'idx_is_active');
            $table->index(['segment_id', 'is_active'], 'idx_segment_active');
            $table->foreign('segment_id', 'fk_firebase_topics_segment')
                ->references('id')
                ->on('segment')
                ->onDelete('set null');
        });

        Schema::table('notification_sent_log', function (Blueprint $table) {
            $table->string('topic_name', 255)->nullable()->after('sent')->comment('Название Firebase топика (если использовался топик)');
            $table->string('segment_ids', 255)->nullable()->after('topic_name')->comment('ID сегментов через запятую');
            $table->enum('send_method', ['batch', 'topic'])->default('topic')->after('segment_ids')->comment('Метод отправки: batch (пакетами) или topic (топики)');
            $table->index('topic_name', 'idx_topic_name');
            $table->index('send_method', 'idx_send_method');
            $table->index('time', 'idx_time');
            $table->index('article_id', 'idx_article_id');
            $table->index(['time', 'send_method'], 'idx_time_method');
        });
    }

    public function down(): void
    {
        Schema::table('notification_sent_log', function (Blueprint $table) {
            $table->dropIndex('idx_topic_name');
            $table->dropIndex('idx_send_method');
            $table->dropIndex('idx_time');
            $table->dropIndex('idx_article_id');
            $table->dropIndex('idx_time_method');
            $table->dropColumn(['topic_name', 'segment_ids', 'send_method']);
        });

        Schema::dropIfExists('firebase_topics');

        Schema::table('segment', function (Blueprint $table) {
            $table->dropUnique('idx_firebase_topic');
            $table->dropColumn('firebase_topic');
        });
    }
};
