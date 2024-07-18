<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GoogleAdsReportService
{
    private $connection;

    public function __construct($connection = null)
    {
        $this->connection = $connection ?? config('database.default');
    }

    private function getTableName($reportType, $accountId, $connection)
    {
        return strtolower("{$connection}_google_adwords_{$reportType}");
    }

    public function createTable($reportType, $accountId, $connection)
    {
        $tableName = $this->getTableName($reportType, $accountId, $connection);
        Schema::connection($connection)->create($tableName, function ($table) use ($reportType) {
            $table->id();
            $table->string('account_name');
            $table->timestamp('report_date');
            $table->date('dated');
            $table->bigInteger('impressions');
            $table->bigInteger('clicks');
            $table->decimal('cost', 10, 2);

            switch ($reportType) {
                case 'campaigns':
                    $table->bigInteger('campaign_id');
                    $table->string('name');
                    $table->string('campaign_status');
                    break;
                case 'adgroups':
                    $table->bigInteger('campaign_id');
                    $table->string('campaign_name');
                    $table->string('campaign_status');
                    $table->bigInteger('adgroup_id');
                    $table->string('name');
                    $table->string('adgroup_status');
                    break;
                case 'ads':
                    $table->bigInteger('campaign_id');
                    $table->string('campaign_name');
                    $table->string('campaign_status');
                    $table->bigInteger('adgroup_id');
                    $table->string('adgroup_name');
                    $table->string('adgroup_status');
                    $table->bigInteger('ad_id');
                    $table->string('name');
                    $table->string('ad_status');
                    $table->string('tracking_url');
                    $table->string('device');
                    break;
                case 'keywords':
                    $table->bigInteger('campaign_id');
                    $table->string('campaign_name');
                    $table->string('campaign_status');
                    $table->bigInteger('adgroup_id');
                    $table->string('adgroup_name');
                    $table->string('adgroup_status');
                    $table->string('match_type');
                    $table->string('keyword_text');
                    $table->string('tracking_url');
                    $table->string('device');
                    break;
            }

            $table->timestamps();
        });
    }

    public function deleteExistingData($reportType, $accountId, $accountName, $startDate, $endDate)
    {
        $tableName = $this->getTableName($reportType, $accountId, $this->connection);
        DB::table($tableName)
            ->where('account_name', $accountName)
            ->whereBetween('dated', [$startDate, $endDate])
            ->delete();
    }
}
