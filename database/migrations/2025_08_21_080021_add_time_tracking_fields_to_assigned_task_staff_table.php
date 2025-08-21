<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assigned_task_staff', function (Blueprint $table) {
            $table->unsignedInteger('duration_in_seconds')->default(0)->after('user_id');
            $table->timestamp('timer_started_at')->nullable()->after('duration_in_seconds');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assigned_task_staff', function (Blueprint $table) {
            $table->dropColumn(['duration_in_seconds', 'timer_started_at', 'created_at', 'updated_at']);
        });
    }
};