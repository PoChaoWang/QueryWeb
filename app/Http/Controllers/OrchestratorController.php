<?php

namespace App\Http\Controllers;

use App\Models\GoogleAdsAccount;
use App\Models\MetaAdsAccount;
use App\Services\GoogleAdsService;
use App\Services\GoogleAdsReportService;
use Carbon\Carbon;
use Google\ApiCore\ApiException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use App\Services\MetaAdsService;
use App\Services\MetaAdsReportService;


class OrchestratorController extends Controller
{
    private $googleAdsService;
    private $googleAdsReportService;
    private $metaAdsService;
    private $metaAdsReportService;

    private function getConnection()
    {
        return session('db_connection', config('database.default'));
    }

    public function __construct(GoogleAdsService $googleAdsService, GoogleAdsReportService $googleAdsReportService,MetaAdsService $metaAdsService, MetaAdsReportService $metaAdsReportService)
    {
        $this->googleAdsService = $googleAdsService;
        $this->googleAdsReportService = $googleAdsReportService;
        $this->metaAdsService = $metaAdsService;
        $this->metaAdsReportService = $metaAdsReportService;
    }

    public function index()
    {
        DB::setDefaultConnection('data_studio');

        $connections = DB::connection('data_studio')->table('client_connections')->pluck('connection_name')->toArray();
        return inertia('Orchestrator/Index', [
            'connections' => $connections,
        ]);
    }

    public function googleEdit()
    {
        $connections = session('db_connection', config('database.default'));
        DB::setDefaultConnection($connections);

        // 獲取與連接相關的所有 Google Ads 帳戶
        $accounts = GoogleAdsAccount::select('id', 'account_name')->get();

        $reportTypes = ['campaigns', 'adgroups', 'ads', 'keywords'];

        return inertia('Orchestrator/GoogleEdit', [
            'accounts' => $accounts,
            'reportTypes' => $reportTypes,
            'connections' => $connections,
            'success' => session('success'),
            'fail' => session('fail'),
        ]);
    }

    public function googleUpdate(Request $request)
    {
        $connections = session('db_connection', config('database.default'));
        DB::setDefaultConnection($connections);
        try {
            $processedData = $this->googleHandleRequest($request);
            \Log::info('Processed raw request data:', $processedData);
            foreach ($processedData['account_ids'] as $index => $accountId) {
                $accountName = $processedData['account_names'][$index];
                foreach ($processedData['report_types'] as $reportType) {
                    \Log::info("Processing report for account: $accountId, report type: $reportType");

                    // 刪除現有數據
                    $this->googleAdsReportService->deleteExistingData(
                        $reportType,
                        $accountId,
                        $accountName,
                        $processedData['start_date'],
                        $processedData['end_date']
                    );

                    // 獲取新數據
                    $reportData = $this->googleAdsService->getReport($accountId, $reportType, $processedData['start_date'], $processedData['end_date']);
                    if (!isset($reportData['fail'])) {
                        $this->googleAdsService->saveReport($reportType, $reportData, $accountId, $accountName);
                    } else {
                        throw new \RuntimeException($reportData['fail']);
                    }
                }
            }

            return redirect()->route('google.edit', ['connections' => $connections])
                ->with('success', "Reports for selected accounts updated successfully");
        } catch (\Exception $e) {
            \Log::error('Error in googleUpdate: ' . $e->getMessage());
            return redirect()->route('google.edit', ['connections' => $connections])
                ->with('fail', "An error occurred: " . $e->getMessage());
        }
    }

    private function googleHandleRequest(Request $request): array
    {
        \Log::info('Received raw request data:', $request->all());

        $data = $request->all();

        // 處理 account_names
        $accountNames = collect($data['account_names'])->map(function ($item) {
            return is_array($item) ? $item['label'] : $item;
        })->toArray();

        // 處理 report_types
        $reportTypes = collect($data['report_types'])->map(function ($item) {
            return is_array($item) ? $item['value'] : $item;
        })->toArray();

        // 處理日期
        $startDate = Carbon::parse($data['start_date'])->format('Y-m-d');
        $endDate = Carbon::parse($data['end_date'])->format('Y-m-d');

        // 驗證數據
        $validator = Validator::make([
            'account_names' => $accountNames,
            'report_types' => $reportTypes,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ], [
            'account_names' => 'required|array',
            'account_names.*' => 'string|max:255',
            'report_types' => 'required|array',
            'report_types.*' => 'string|in:campaigns,adgroups,ads,keywords',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        // 獲取 account IDs
        $accountIds = GoogleAdsAccount::whereIn('account_name', $accountNames)->pluck('account_id')->toArray();
        if (count($accountIds) !== count($accountNames)) {
            $missingAccounts = array_diff($accountNames, GoogleAdsAccount::whereIn('account_name', $accountNames)->pluck('account_name')->toArray());
            throw new ModelNotFoundException("Some accounts not found: " . implode(', ', $missingAccounts));
        }

        return [
            'account_ids' => $accountIds,
            'account_names' => $accountNames,
            'report_types' => $reportTypes,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    public function metaEdit()
    {
        $connections = session('db_connection', config('database.default'));
        DB::setDefaultConnection($connections);

        // 获取与连接相关的所有 Meta Ads 账户
        $accounts = MetaAdsAccount::select('id', 'account_name')->get();

        $reportTypes = ['campaigns', 'adsets', 'ads'];

        return inertia('Orchestrator/MetaEdit', [
            'accounts' => $accounts,
            'reportTypes' => $reportTypes,
            'connections' => $connections,
            'success' => session('success'),
            'fail' => session('fail'),
        ]);
    }

    public function metaUpdate(Request $request)
    {
        $connections = session('db_connection', config('database.default'));
        DB::setDefaultConnection($connections);
        $errors = [];
        $successCount = 0;

        try {
            $processedData = $this->metaHandleRequest($request);
            \Log::info('Processed raw request data:', $processedData);

            foreach ($processedData['account_names'] as $index => $accountName) {
                $accountId = $processedData['account_ids'][$index];
                foreach ($processedData['report_types'] as $reportType) {
                    \Log::info("Processing report for account: $accountName (ID: $accountId), report type: $reportType");

                    try {
                        // 删除现有数据
                        $this->metaAdsReportService->deleteExistingData(
                            $reportType,
                            $accountName,
                            $processedData['start_date'],
                            $processedData['end_date']
                        );

                        // 获取新数据
                        $reportData = $this->metaAdsService->getReport([$accountId], $reportType, $processedData['start_date'], $processedData['end_date']);

                        if (isset($reportData[$accountId]['error'])) {
                            throw new \Exception($reportData[$accountId]['error']);
                        }

                        $this->metaAdsService->saveReport($reportType, $reportData[$accountId], $accountId, $processedData['start_date'], $processedData['end_date']);
                        $successCount++;

                    } catch (\Facebook\Exceptions\FacebookResponseException $e) {
                        $errorMessage = "Facebook API Error for $accountName (ID: $accountId): " . $e->getMessage();
                        \Log::error($errorMessage);
                        \Log::error('Error Code: ' . $e->getCode());
                        \Log::error('Error Subcode: ' . $e->getSubErrorCode());
                        $errors[] = $errorMessage;

                    } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                        $errorMessage = "Facebook SDK Error for $accountName (ID: $accountId): " . $e->getMessage();
                        \Log::error($errorMessage);
                        $errors[] = $errorMessage;

                    } catch (\Exception $e) {
                        $errorMessage = "Error processing $reportType for $accountName (ID: $accountId): " . $e->getMessage();
                        \Log::error($errorMessage);
                        $errors[] = $errorMessage;
                    }
                }
            }

            if (empty($errors)) {
                return redirect()->route('meta.edit', ['connections' => $connections])
                    ->with('success', "Reports for all selected Meta accounts updated successfully");
            } else {
                $errorMessage = implode("\n", $errors);
                $successMessage = $successCount > 0 ? "Successfully updated $successCount report(s). " : "";
                return redirect()->route('meta.edit', ['connections' => $connections])
                    ->with('warning', $successMessage . "Some errors occurred: \n" . $errorMessage);
            }

        } catch (\Exception $e) {
            \Log::error('Error in metaUpdate: ' . $e->getMessage());
            return redirect()->route('meta.edit', ['connections' => $connections])
                ->with('fail', "An error occurred: " . $e->getMessage());
        }
    }

private function metaHandleRequest(Request $request): array
{
    \Log::info('Received raw request data for Meta:', $request->all());

    $data = $request->all();

    // 处理 account_names
    $accountNames = collect($data['account_names'])->map(function ($item) {
        return is_array($item) ? $item['label'] : $item;
    })->toArray();

    // 处理 report_types
    $reportTypes = collect($data['report_types'])->map(function ($item) {
        return is_array($item) ? $item['value'] : $item;
    })->toArray();

    // 处理日期
    $startDate = Carbon::parse($data['start_date'])->format('Y-m-d');
    $endDate = Carbon::parse($data['end_date'])->format('Y-m-d');

    // 验证数据
    $validator = Validator::make([
        'account_names' => $accountNames,
        'report_types' => $reportTypes,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ], [
        'account_names' => 'required|array',
        'account_names.*' => 'string|max:255',
        'report_types' => 'required|array',
        'report_types.*' => 'string|in:campaigns,adsets,ads',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    if ($validator->fails()) {
        throw new \InvalidArgumentException($validator->errors()->first());
    }

    // 獲取 account IDs
    $accountIds = DB::table('meta_ads_accounts')
    ->whereIn('account_name', $accountNames)
    ->pluck('account_id')
    ->toArray();
    \Log::info('Found account IDs:', $accountIds);
    if (count($accountIds) !== count($accountNames)) {
        $missingAccounts = array_diff($accountNames, MetaAdsAccount::whereIn('account_name', $accountNames)->pluck('account_name')->toArray());
        throw new ModelNotFoundException("Some accounts not found: " . implode(', ', $missingAccounts));
    }

    return [
        'account_names' => $accountNames,
        'account_ids' => $accountIds,
        'report_types' => $reportTypes,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ];
}
}
