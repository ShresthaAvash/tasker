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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->string('name');
            $table->text('description')->nullable();
            // Deadline offset from the job's start date
            $table->integer('deadline_offset')->default(0); 
            // unit for the offset, e.g., 'days', 'weeks'
            $table->string('deadline_unit')->default('days'); 
            // We assign to a designation (role) in the template, not a specific person
            $table->unsignedBigInteger('staff_designation_id')->nullable(); 
            $table->timestamps();

            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
            $table->foreign('staff_designation_id')->references('id')->on('staff_designations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};