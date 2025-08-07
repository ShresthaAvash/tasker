<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This will add the 'start' and 'end' columns to the 'tasks' table.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dateTime('start')->nullable()->after('description');
            $table->dateTime('end')->nullable()->after('start');
        });
    }

    /**
     * Reverse the migrations.
     * This will remove the 'start' and 'end' columns if you ever need to undo the migration.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['start', 'end']);
        });
    }
};