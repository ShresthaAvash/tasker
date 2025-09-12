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
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable();
            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();
            $table->integer('deadline_offset')->default(0); 
            $table->string('deadline_unit')->default('days'); 
            $table->unsignedBigInteger('staff_designation_id')->nullable(); 
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('status')->default('not_started');
            $table->json('completed_at_dates')->nullable();
            $table->unsignedInteger('duration_in_seconds')->default(0);
            $table->timestamp('timer_started_at')->nullable();
            $table->string('color')->nullable();
            $table->json('color_overrides')->nullable();
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('staff_designation_id')->references('id')->on('staff_designations')->onDelete('set null');
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('set null');
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