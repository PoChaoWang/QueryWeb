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
        Schema::connection('suntory')->create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('query_id')->constrained('queries')->onDelete('cascade');
            $table->enum('week_day', ['Everybody', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
            $table->time('time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('suntory')->dropIfExists('schedules');
    }
};
