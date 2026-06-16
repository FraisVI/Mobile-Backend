<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('segment', function (Blueprint $table) {
            $table->index('uuid', 'idx_uuid');
            $table->index('name', 'idx_name');
        });
    }

    public function down(): void
    {
        Schema::table('segment', function (Blueprint $table) {
            $table->dropIndex('idx_uuid');
            $table->dropIndex('idx_name');
        });
    }
};
