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
        Schema::table('tasks', function (Blueprint $table) {
            // This column will store user-specific colors as a JSON object
            // e.g., {"user_id_1": "#ff0000", "user_id_2": "#00ff00"}
            $table->json('color_overrides')->nullable()->after('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('color_overrides');
        });
    }
};