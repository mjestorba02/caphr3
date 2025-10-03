<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('time_trackings', function (Blueprint $table) {
            $table->decimal('overtime', 5, 2)->default(0)->after('total_hours');
        });
    }

    public function down()
    {
        Schema::table('time_trackings', function (Blueprint $table) {
            $table->dropColumn('overtime');
        });
    }
};
