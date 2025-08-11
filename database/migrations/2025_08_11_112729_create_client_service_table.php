<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_service', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // This is the client's ID
            $table->unsignedBigInteger('service_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            
            $table->unique(['user_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_service');
    }
};