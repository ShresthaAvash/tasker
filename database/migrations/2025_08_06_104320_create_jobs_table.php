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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            
            // ✅ THIS IS THE MISSING COLUMN
            $table->unsignedBigInteger('service_id'); 
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_event')->default('job_date'); 
            $table->string('repeat_frequency')->default('annually');
            $table->timestamps();

            // ✅ THIS IS THE MISSING FOREIGN KEY RELATIONSHIP
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};