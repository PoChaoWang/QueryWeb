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
        $tables = ['campaigns', 'adsets', 'ads'];
        $connection = config('database.default');
        $databaseName = config("database.connections.{$connection}.database");

        foreach ($tables as $table) {
            $tableName = strtolower("{$databaseName}_meta_ads_{$table}");

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['account_name']);
                $table->index('account_name');
            });
        }
    }

    public function down()
    {
        $tables = ['campaigns', 'adsets', 'ads'];
        $connection = config('database.default');
        $databaseName = config("database.connections.{$connection}.database");

        foreach ($tables as $table) {
            $tableName = strtolower("{$databaseName}_meta_ads_{$table}");

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex(['account_name']);
                $table->foreign('account_name')->references('account_name')->on('meta_ads_accounts')->onDelete('cascade');
            });
        }
    }
};
