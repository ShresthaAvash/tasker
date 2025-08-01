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
            $table->integer('service_id')->nullable();
            $table->integer('job_id')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('inactive');
            $table->string('is_recurring')->default('inactive');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('hours')->nullable();
            $table->decimal('cost')->nullable();
            $table->string('duration')->nullable();
            $table->integer('assignee_id')->nullable();
            $table->integer('assigner_id')->nullable();
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('job_id')->references('id')->on('jobs');
            $table->foreign('assignee_id')->references('id')->on('users');
            $table->foreign('assigner_id')->references('id')->on('users');
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
