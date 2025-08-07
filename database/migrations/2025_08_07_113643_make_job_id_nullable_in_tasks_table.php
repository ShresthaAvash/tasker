<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // This changes the job_id column to allow it to be empty (NULL).
            $table->unsignedBigInteger('job_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // This reverts the change if needed.
            $table->unsignedBigInteger('job_id')->nullable(false)->change();
        });
    }
};