<?php

namespace App\Services;

use Google\Ads\GoogleAds\Lib\V17\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\V17\Services\GoogleAdsServiceClient;
use Google\ApiCore\ApiException;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Illuminate\Support\Facades\DB;
use Google\Ads\GoogleAds\V17\Services\SearchGoogleAdsStreamRequest;

class GoogleAdsService
{
    private $googleAdsClient;

    public function __construct()
    {
        $oAuth2Credential = (new OAuth2TokenBuilder())
            ->withClientId(env('GOOGLE_ADS_CLIENT_ID'))
            ->withClientSecret(env('GOOGLE_ADS_CLIENT_SECRET'))
            ->withRefreshToken(env('GOOGLE_ADS_REFRESH_TOKEN'))
            ->build();

        $this->googleAdsClient = (new GoogleAdsClientBuilder())
            ->withOAuth2Credential($oAuth2Credential)
            ->withDeveloperToken(env('GOOGLE_ADS_DEVELOPER_TOKEN'))
            ->withLoginCustomerId(env('GOOGLE_ADS_LOGIN_CUSTOMER_ID'))
            ->build();
    }

    private function executeQuery($customerId, $query)
    {
        $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();

        try {
            // 創建 SearchGoogleAdsStreamRequest 對象
            $request = new SearchGoogleAdsStreamRequest([
                'customer_id' => $customerId,
                'query' => $query
            ]);

            // 使用創建的請求對象調用 searchStream
            $stream = $googleAdsServiceClient->searchStream($request);

            $results = [];
            foreach ($stream->iterateAllElements() as $response) {
                foreach ($response->getResults() as $row) {
                    $results[] = $row;
                }
            }
            \Log::info("Query results for customer ID " . $customerId . ":", ['count' => count($results), 'sample' => print_r($results[0] ?? null, true)]);
            return $results;
        } catch (ApiException $apiException) {
            return ['error' => $apiException->getMessage()];
        }
    }


    public function getReport($customerId, $reportType, $startDate, $endDate)
    {
        $query = $this->buildQuery($reportType, $startDate, $endDate);
        return $this->executeQuery($customerId, $query);
    }

    private function buildQuery($reportType, $startDate, $endDate)
    {
        $baseQuery = $this->getBaseQuery($reportType);
        return $baseQuery . " WHERE segments.date BETWEEN '$startDate' AND '$endDate'";
    }

    private function getBaseQuery($reportType)
    {
        switch ($reportType) {
            case 'campaigns':
                return "
                    SELECT
                        segments.date,
                        campaign.id,
                        campaign.name,
                        campaign.status,
                        metrics.impressions,
                        metrics.clicks,
                        metrics.cost_micros
                    FROM campaign
                ";
            case 'adgroups':
                return "
                    SELECT
                        segments.date,
                        campaign.id,
                        campaign.name,
                        campaign.status,
                        ad_group.id,
                        ad_group.name,
                        ad_group.status,
                        metrics.impressions,
                        metrics.clicks,
                        metrics.cost_micros
                    FROM ad_group
                ";
            case 'ads':
                return "
                    SELECT
                        segments.date,
                        campaign.id,
                        campaign.name,
                        campaign.status,
                        ad_group.id,
                        ad_group.name,
                        ad_group.status,
                        ad_group_ad.ad.id,
                        ad_group_ad.ad.name,
                        ad_group_ad.status,
                        ad_group_ad.ad.tracking_url_template,
                        segments.device,
                        metrics.impressions,
                        metrics.clicks,
                        metrics.cost_micros
                    FROM ad_group_ad
                ";
            case 'keywords':
                return "
                    SELECT
                        segments.date,
                        campaign.id,
                        campaign.name,
                        campaign.status,
                        ad_group.id,
                        ad_group.name,
                        ad_group.status,
                        ad_group_criterion.keyword.match_type,
                        ad_group_criterion.keyword.text,
                        ad_group_criterion.tracking_url_template,
                        segments.device,
                        metrics.impressions,
                        metrics.clicks,
                        metrics.cost_micros
                    FROM keyword_view
                ";
            default:
                throw new \InvalidArgumentException("Invalid report type: $reportType");
        }
    }

    public function saveReport($reportType, $data, $accountId, $accountName)
    {
        $tableName = $this->getTableName($reportType, $accountId);
        $this->insertData($tableName, $data, $accountName);
    }

    private function getTableName($reportType, $accountId)
    {
        $databaseName = config("database.connection." . DB::getDefaultConnection() . ".database");
        return strtolower("{$databaseName}_googleAds_{$accountId}_{$reportType}");
    }

    private function insertData($tableName, $data, $accountName)
    {
        $rows = [];
        foreach ($data as $row) {
            $formattedRow = $this->formatRowData($row, $tableName, $accountName);
            if ($formattedRow !== null) {
                $rows[] = $formattedRow;
            }
        }

        if (!empty($rows)) {
            DB::table($tableName)->insert($rows);
        } else {
            \Log::warning("No valid data to insert for table: " . $tableName);
        }
    }

    private function formatRowData($row, $tableName, $accountName)
    {
        // 檢查 $row 是否為字符串
        if (is_string($row)) {
            // 如果是字符串，可能是錯誤消息
            \Log::error("Unexpected data format in formatRowData: " . $row);
            return null;
        }

        // 檢查 $row 是否有必要的方法
        if (!method_exists($row, 'getSegments') || !method_exists($row, 'getMetrics')) {
            \Log::error("Row object does not have expected methods: " . print_r($row, true));
            return null;
        }

        $formattedRow = [
            'report_date' => now(),
            'dated' => $row->getSegments()->getDate(),
            'impressions' => $row->getMetrics()->getImpressions(),
            'clicks' => $row->getMetrics()->getClicks(),
            'cost' => $row->getMetrics()->getCostMicros() / 1000000,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (strpos($tableName, 'campaigns') !== false) {
            $formattedRow['campaign_id'] = $row->getCampaign()->getId();
            $formattedRow['name'] = $row->getCampaign()->getName();
            $formattedRow['campaign_status'] = $row->getCampaign()->getStatus();
        } elseif (strpos($tableName, 'adgroups') !== false) {
            $formattedRow['campaign_id'] = $row->getCampaign()->getId();
            $formattedRow['campaign_name'] = $row->getCampaign()->getName();
            $formattedRow['campaign_status'] = $row->getCampaign()->getStatus();
            $formattedRow['adgroup_id'] = $row->getAdGroup()->getId();
            $formattedRow['name'] = $row->getAdGroup()->getName();
            $formattedRow['adgroup_status'] = $row->getAdGroup()->getStatus();
        } elseif (strpos($tableName, 'ads') !== false) {
            $formattedRow['campaign_id'] = $row->getCampaign()->getId();
            $formattedRow['campaign_name'] = $row->getCampaign()->getName();
            $formattedRow['campaign_status'] = $row->getCampaign()->getStatus();
            $formattedRow['adgroup_id'] = $row->getAdGroup()->getId();
            $formattedRow['adgroup_name'] = $row->getAdGroup()->getName();
            $formattedRow['adgroup_status'] = $row->getAdGroup()->getStatus();
            $formattedRow['ad_id'] = $row->getAdGroupAd()->getAd()->getId();
            $formattedRow['name'] = $row->getAdGroupAd()->getAd()->getName();
            $formattedRow['ad_status'] = $row->getAdGroupAd()->getStatus();
            $formattedRow['tracking_url'] = $row->getAdGroupAd()->getAd()->getFinalUrls()[0] ?? '';
            $formattedRow['device'] = $row->getSegments()->getDevice();
        } elseif (strpos($tableName, 'keywords') !== false) {
            $formattedRow['campaign_id'] = $row->getCampaign()->getId();
            $formattedRow['campaign_name'] = $row->getCampaign()->getName();
            $formattedRow['campaign_status'] = $row->getCampaign()->getStatus();
            $formattedRow['adgroup_id'] = $row->getAdGroup()->getId();
            $formattedRow['adgroup_name'] = $row->getAdGroup()->getName();
            $formattedRow['adgroup_status'] = $row->getAdGroup()->getStatus();
            $formattedRow['match_type'] = $row->getAdGroupCriterion()->getKeyword()->getMatchType();
            $formattedRow['keyword_text'] = $row->getAdGroupCriterion()->getKeyword()->getText();
            $formattedRow['tracking_url'] = $row->getAdGroupCriterion()->getFinalUrls()[0] ?? '';
            $formattedRow['device'] = $row->getSegments()->getDevice();
        }
        $formattedRow['account_name'] = $accountName;

        return $formattedRow;
    }
}
