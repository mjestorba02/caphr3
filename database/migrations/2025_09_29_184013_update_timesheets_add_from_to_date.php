<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->date('from_date')->nullable()->after('employee_id');
            $table->date('to_date')->nullable()->after('from_date');
            $table->dropColumn('date');
        });
    }

    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->date('date')->after('employee_id');
            $table->dropColumn(['from_date', 'to_date']);
        });
    }
};
