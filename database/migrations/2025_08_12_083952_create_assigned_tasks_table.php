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
$table->unsignedBigInteger('task_template_id'); // Link to the original task
$table->string('name');
$table->date('due_date')->nullable();
$table->string('status')->default('pending');
$table->timestamps();

$table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
$table->foreign('task_template_id')->references('id')->on('tasks')->onDelete('cascade');
});
}

public function down(): void
{
Schema::dropIfExists('assigned_tasks');
}
};