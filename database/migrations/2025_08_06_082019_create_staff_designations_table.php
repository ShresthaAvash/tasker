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
        Schema::create('staff_designations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('organization_id');
            $table->timestamps();

            // Foreign key to link designations to the organization that created them
            $table->foreign('organization_id')->references('id')->on('users')->onDelete('cascade');

            // A designation name should be unique for each organization
            $table->unique(['name', 'organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_designations');
    }
};