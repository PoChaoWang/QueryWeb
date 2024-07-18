<?php

namespace App\Services;

use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\AdAccount;
use Illuminate\Support\Facades\DB;

class MetaAdsService
{
    private $api;
    private $connection;

    public function __construct($connection = null)
    {
        $this->connection = $connection ?? config('database.default');
        $this->api = Api::init(
            env('FACEBOOK_APP_ID'),
            env('FACEBOOK_APP_SECRET'),
            env('FACEBOOK_ACCESS_TOKEN')
        );
        $this->api->setLogger(new CurlLogger());
    }

    public function getReport($accountIds, $reportType, $startDate, $endDate)
    {
        $results = [];
        foreach ($accountIds as $accountId) {
            try {
                $data = $this->fetchReportData($accountId, $reportType, $startDate, $endDate);
                if (isset($data['error'])) {
                    throw new \Exception($data['error']);
                }
                $results[$accountId] = $data;
            } catch (\Facebook\Exceptions\FacebookResponseException $e) {
                $errorMessage = "Facebook API Error for account $accountId: " . $e->getMessage();
                \Log::error($errorMessage);
                \Log::error('Error Code: ' . $e->getCode());
                \Log::error('Error Subcode: ' . $e->getSubErrorCode());
                \Log::error('Raw Response: ' . $e->getRawResponse());
                $results[$accountId] = ['error' => $errorMessage];
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                $errorMessage = "Facebook SDK Error for account $accountId: " . $e->getMessage();
                \Log::error($errorMessage);
                \Log::error('Stack Trace: ' . $e->getTraceAsString());
                $results[$accountId] = ['error' => $errorMessage];
            } catch (\Exception $e) {
                $errorMessage = "General Error for account $accountId: " . $e->getMessage();
                \Log::error($errorMessage);
                \Log::error('Stack Trace: ' . $e->getTraceAsString());
                $results[$accountId] = ['error' => $errorMessage];
            }
        }
        return $results;
    }

    private function getAccountIds($accountIds)
    {
        return DB::table('meta_ads_accounts')
            ->whereIn('account_name', $accountIds)
            ->select('account_id', 'account_name')
            ->get();
    }

    private function fetchReportData($accountId, $reportType, $startDate, $endDate)
    {
        $adAccount = new AdAccount($accountId);

        $fields = [
            'campaign_name',
            'adset_name',
            'ad_name',
            'impressions',
            'link_clicks',
            'spend',
        ];

        $params = [
            'time_range' => ['since' => $startDate, 'until' => $endDate],
            'filtering' => [['field' => 'impressions', 'operator' => 'GREATER_THAN', 'value' => '0']],
            'level' => $this->getReportLevel($reportType),
            'breakdowns' => ['day'],
        ];

        try {
            $insights = $adAccount->getInsights($fields, $params);
            return $insights->getResponse()->getContent();
        } catch (\Exception $e) {
            // Handle API errors
            return ['error' => $e->getMessage()];
        }
    }

    private function getReportLevel($reportType)
    {
        switch ($reportType) {
            case 'campaigns':
                return 'campaign';
            case 'adsets':
                return 'adset';
            case 'ads':
                return 'ad';
            default:
                throw new \InvalidArgumentException("Invalid report type: $reportType");
        }
    }

    public function saveReport($reportType, $data, $accountName, $startDate, $endDate)
    {
        $tableName = $this->getTableName($reportType);

        // 删除现有的数据
        DB::table($tableName)
            ->where('account_name', $accountName)
            ->whereBetween('dated', [$startDate, $endDate])
            ->delete();

        // 插入新数据
        $rows = $this->formatData($data, $reportType, $accountName);
        if (!empty($rows)) {
            DB::table($tableName)->insert($rows);
        }
    }

    private function getTableName($reportType)
    {
        $databaseName = config("database.connections.{$this->connection}.database");
        return strtolower("{$databaseName}_meta_ads_{$reportType}");
    }

    private function formatData($data, $reportType, $accountName)
    {
        $formattedRows = [];
        foreach ($data as $row) {
            // 确保 $row 是一个数组
            if (!is_array($row)) {
                \Log::warning("Unexpected data format in row for account $accountName: " . json_encode($row));
                continue;
            }

            $formattedRow = [
                'dated' => $row['date_start'] ?? null,
                'account_name' => $accountName,
                'impressions' => $row['impressions'] ?? 0,
                'link_clicks' => $row['clicks'] ?? 0,
                'spend' => $row['spend'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            switch ($reportType) {
                case 'campaigns':
                    $formattedRow['campaign_id'] = $row['campaign_id'] ?? null;
                    $formattedRow['campaign_status'] = $row['campaign_name'] ?? null;
                    break;
                case 'adsets':
                    $formattedRow['campaign_id'] = $row['campaign_id'] ?? null;
                    $formattedRow['campaign_name'] = $row['campaign_name'] ?? null;
                    $formattedRow['adset_id'] = $row['adset_id'] ?? null;
                    $formattedRow['name'] = $row['adset_name'] ?? null;
                    break;
                case 'ads':
                    $formattedRow['campaign_id'] = $row['campaign_id'] ?? null;
                    $formattedRow['campaign_name'] = $row['campaign_name'] ?? null;
                    $formattedRow['adset_id'] = $row['adset_id'] ?? null;
                    $formattedRow['adset_name'] = $row['adset_name'] ?? null;
                    $formattedRow['ad_id'] = $row['ad_id'] ?? null;
                    $formattedRow['name'] = $row['ad_name'] ?? null;
                    break;
            }

            $formattedRows[] = $formattedRow;
        }

        return $formattedRows;
    }
}
