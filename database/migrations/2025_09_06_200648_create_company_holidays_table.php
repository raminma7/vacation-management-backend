<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->enum('type', ['company', 'national'])->default('company');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('company_holidays');
    }
};
