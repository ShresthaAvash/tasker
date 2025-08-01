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
        Schema::create('client_notes', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id')->nullable();
            $table->string('client_note_title')->nullable();
            $table->string('client_note_content')->nullable();
            $table->date('client_note_date')->nullable();
            $table->string('client_note_status')->nullable('inactive');
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_notes');
    }
};
