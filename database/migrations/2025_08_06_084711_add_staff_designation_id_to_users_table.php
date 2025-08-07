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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_designation_id')->nullable()->after('type');

            // Foreign key to link staff to their designation
            $table->foreign('staff_designation_id')
                  ->references('id')
                  ->on('staff_designations')
                  ->onDelete('set null'); // If a designation is deleted, set it to NULL for the user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['staff_designation_id']);
            $table->dropColumn('staff_designation_id');
        });
    }
};