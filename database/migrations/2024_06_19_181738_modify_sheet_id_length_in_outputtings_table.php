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
        Schema::table('outputtings', function (Blueprint $table) {
            $table->string('sheet_id', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outputtings', function (Blueprint $table) {
            $table->string('sheet_id', 100)->change(); // 假设原来的长度是100
        });
    }
};
