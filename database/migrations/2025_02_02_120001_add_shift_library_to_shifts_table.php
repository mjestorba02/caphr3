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
        Schema::table('shifts', function (Blueprint $table) {
            // Add shift_library_id before shift_type
            $table->unsignedBigInteger('shift_library_id')->nullable()->after('employee_id');
            $table->foreign('shift_library_id')->references('id')->on('shift_libraries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['shift_library_id']);
            $table->dropColumn('shift_library_id');
        });
    }
};
