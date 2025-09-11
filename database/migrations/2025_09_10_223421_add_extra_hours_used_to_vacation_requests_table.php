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
        Schema::table('vacation_requests', function (Blueprint $table) {
            $table->integer('extra_hours_used')->default(0)->after('status'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacation_requests', function (Blueprint $table) {
            $table->dropColumn('extra_hours_used');
        });
    }
};
