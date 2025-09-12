<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assigned_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('task_template_id'); // Link to the original task
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('to_do');
            $table->json('completed_at_dates')->nullable();
            $table->unsignedInteger('duration_in_seconds')->default(0);
            $table->timestamp('timer_started_at')->nullable();
            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable();
            $table->string('color')->nullable();
            $table->json('color_overrides')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('task_template_id')->references('id')->on('tasks')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assigned_tasks');
    }
};