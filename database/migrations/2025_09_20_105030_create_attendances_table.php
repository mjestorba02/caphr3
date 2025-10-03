<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('time_trackings', function (Blueprint $table) {
            $table->id();

            // ✅ Reference to employee (Employee ID)
            $table->unsignedBigInteger('employee_id');

            // ✅ Attendance info
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();

            // ✅ Total hours worked
            $table->decimal('total_hours', 5, 2)->default(0);

            // ✅ Attendance status
            $table->enum('status', ['Present','Absent','Late','Leave'])->default('Present');

            $table->timestamps();

            // Foreign key
            $table->foreign('employee_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_trackings');
    }
};