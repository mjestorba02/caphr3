<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id'); // link to users table
            $table->date('date');
            $table->string('day'); // e.g., Monday, Tuesday
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('lunch_break', 4, 2)->default(1); // hrs
            $table->decimal('hours_worked', 5, 2)->default(0); // total excluding breaks
            $table->decimal('overtime', 5, 2)->default(0);
            $table->string('position')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};