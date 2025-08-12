<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
Schema::create('assigned_task_staff', function (Blueprint $table) {
$table->unsignedBigInteger('assigned_task_id');
$table->unsignedBigInteger('user_id'); // Staff's ID

$table->foreign('assigned_task_id')->references('id')->on('assigned_tasks')->onDelete('cascade');
$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

$table->primary(['assigned_task_id', 'user_id']);
});
}

public function down(): void
{
Schema::dropIfExists('assigned_task_staff');
}
};