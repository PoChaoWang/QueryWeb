<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class MetaAdsReportService
{
    private $connection;

    public function __construct($connection = null)
    {
        $this->connection = $connection ?? config('database.default');
    }

    private function getTableName($reportType)
    {
        $databaseName = config("database.connections.{$this->connection}.database");
        return strtolower("{$databaseName}_meta_ads_{$reportType}");
    }

    public function createTableIfNotExists($reportType)
    {
        $tableName = $this->getTableName($reportType);

        if (!Schema::connection($this->connection)->hasTable($tableName)) {
            Schema::connection($this->connection)->create($tableName, function (Blueprint $table) use ($reportType) {
                $table->id();
                $table->date('dated');
                $table->string('account_name')->index(); // 添加索引而不是外鍵

                switch ($reportType) {
                    case 'campaigns':
                        $this->createCampaignsTable($table);
                        break;
                    case 'adsets':
                        $this->createAdsetsTable($table);
                        break;
                    case 'ads':
                        $this->createAdsTable($table);
                        break;
                }

                $table->timestamps();
                // 移除外鍵約束
                // $table->foreign('account_name')->references('account_name')->on('meta_ads_accounts')->onDelete('cascade');
            });
        }
    }

    private function createCampaignsTable(Blueprint $table)
    {
        $table->unsignedBigInteger('campaign_id');
        $table->string('campaign_status');
        $table->unsignedBigInteger('impressions');
        $table->unsignedBigInteger('link_clicks');
        $table->decimal('spend', 10, 2);
    }

    private function createAdsetsTable(Blueprint $table)
    {
        $table->unsignedBigInteger('campaign_id');
        $table->string('campaign_name');
        $table->string('adset_id');
        $table->string('name');
        $table->string('ad_group_status');
        $table->unsignedBigInteger('impressions');
        $table->unsignedBigInteger('link_clicks');
        $table->decimal('spend', 10, 2);
    }

    private function createAdsTable(Blueprint $table)
    {
        $table->unsignedBigInteger('campaign_id');
        $table->string('campaign_name');
        $table->string('adset_id');
        $table->string('adset_name');
        $table->unsignedBigInteger('ad_id');
        $table->string('name');
        $table->string('ad_status');
        $table->string('device');
        $table->unsignedBigInteger('impressions');
        $table->unsignedBigInteger('link_clicks');
        $table->decimal('spend', 10, 2);
    }

    public function deleteExistingData($reportType, $accountName, $startDate, $endDate)
    {
        $tableName = $this->getTableName($reportType);

        DB::table($tableName)
            ->where('account_name', $accountName)
            ->whereBetween('dated', [$startDate, $endDate])
            ->delete();
    }
}
