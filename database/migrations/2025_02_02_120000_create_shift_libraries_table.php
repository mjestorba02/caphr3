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
        Schema::create('shift_libraries', function (Blueprint $table) {
            $table->id();
            $table->string('shift_name')->unique(); // e.g., "Morning Shift", "Afternoon Shift", "Night Shift"
            $table->time('start_time');
            $table->time('end_time');
            $table->string('break_time')->nullable(); // e.g., "1h", "30m"
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_libraries');
    }
};
