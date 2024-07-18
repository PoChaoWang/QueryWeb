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
        Schema::connection('suntory')->create('queries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('query_sql');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('data_studio.users');
            $table->foreign('updated_by')->references('id')->on('data_studio.users');
        });
    }

    public function down()
    {
        Schema::connection('suntory')->dropIfExists('queries');
    }
};
