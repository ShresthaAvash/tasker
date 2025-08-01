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
        Schema::create('client_extras', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id')->nullable();
            $table->integer('com_dealing_manager_id')->nullable();
            $table->integer('com_business_area_id')->nullable();
            $table->string('com_website')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_address')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('users');
            $table->foreign('com_business_area_id')->references('id')->on('business_areas');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_extras');
    }
};
