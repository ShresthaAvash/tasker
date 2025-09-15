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
Schema::table('client_service', function (Blueprint $table) {
// This status is for reporting purposes and is independent of the main service status.
$table->string('status')->default('Not Started')->after('end_date');
});
}

/**
* Reverse the migrations.
*/
public function down(): void
{
Schema::table('client_service', function (Blueprint $table) {
$table->dropColumn('status');
});
}
};