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
        Schema::table('assigned_tasks', function (Blueprint $table) {
            // Add columns to link the assigned task back to its service and job template
            $table->unsignedBigInteger('service_id')->after('client_id');
            $table->unsignedBigInteger('job_id')->after('service_id');
            $table->text('description')->nullable()->after('name');

            // Add foreign key constraints for data integrity
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assigned_tasks', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropForeign(['job_id']);
            $table->dropColumn(['service_id', 'job_id', 'description']);
        });
    }
};