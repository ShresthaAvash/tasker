<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedInteger('duration_in_seconds')->default(0)->after('status');
            $table->timestamp('timer_started_at')->nullable()->after('duration_in_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['duration_in_seconds', 'timer_started_at']);
        });
    }
};