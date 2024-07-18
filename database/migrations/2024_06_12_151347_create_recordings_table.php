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
        Schema::connection('suntory')->create('recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('query_id')->constrained('queries')->onDelete('cascade');
            $table->unsignedBigInteger('updated_by');
            $table->string('csv_file_path')->nullable();
            $table->string('query_sql');
            $table->string('status')->default('processing');
            $table->string('fail_reason')->nullable();
            $table->string('status')->default('processing');
            $table->string('fail_reason')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('data_studio.users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('suntory')->dropIfExists('recordings');
    }
};
