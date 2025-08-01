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
        Schema::create('client_documents', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id')->nullable();
            $table->integer('service_id')->nullable();
            $table->integer('job_id')->nullable();
            $table->integer('task_id')->nullable();
            $table->string('client_document_title')->nullable();
            $table->string('client_document_file_url')->nullable();
            $table->date('client_document_upload_date')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('users');
            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('job_id')->references('id')->on('jobs');
            $table->foreign('task_id')->references('id')->on('tasks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_documents');
    }
};
