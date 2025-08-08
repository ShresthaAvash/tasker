<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Fields for recurring tasks
            $table->boolean('is_recurring')->default(false)->after('description');
            $table->string('recurring_frequency')->nullable()->after('is_recurring'); // daily, weekly, monthly

            // Field for direct staff assignment (links to the users table)
            $table->unsignedBigInteger('staff_id')->nullable()->after('staff_designation_id');
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropColumn(['is_recurring', 'recurring_frequency', 'staff_id']);
        });
    }
};