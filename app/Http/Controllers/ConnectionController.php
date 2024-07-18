<?php

namespace App\Http\Controllers;


use App\Http\Requests\StoreGoogleAdsAccountRequest;
use App\Http\Requests\StoreMetaAdsAccountRequest;
use App\Models\GoogleAdsAccount;
use App\Models\MetaAdsAccount;
use Illuminate\Support\Facades\DB;
use App\Services\GoogleAdsService;
use App\Services\GoogleAdsReportService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Services\MetaAdsService;
use App\Services\MetaAdsReportService;

class ConnectionController extends Controller
{
    private $googleAdsService;
    private $googleAdsReportService;
    private $metaAdsService;
    private $metaAdsReportService;

    public function __construct(
        GoogleAdsService $googleAdsService,
        GoogleAdsReportService $googleAdsReportService,
        MetaAdsService $metaAdsService,
        MetaAdsReportService $metaAdsReportService
    ) {
        $this->googleAdsService = $googleAdsService;
        $this->googleAdsReportService = $googleAdsReportService;
        $this->metaAdsService = $metaAdsService;
        $this->metaAdsReportService = $metaAdsReportService;
    }

    private function getConnection()
    {
        return session('db_connection', config('database.default'));
    }

    public function index()
    {
        DB::setDefaultConnection('data_studio');

        $connections = DB::connection('data_studio')->table('client_connections')->pluck('connection_name')->toArray();
        return inertia('Connection/Index', [
            'connections' => $connections,
        ]);
    }
//google start
    public function googleShow()
    {
        // 設置連接
        $connections = $this->getConnection();
        DB::setDefaultConnection($connections);

        // 獲取當前數據庫名稱
        $currentDatabase = DB::getDatabaseName();
        $googleAccounts = DB::table('google_ads_accounts')->get(['account_id', 'account_name', 'created_at']);

        return inertia('Connection/GoogleShow', [
            'connections' => $connections,
            'currentDatabase' => $currentDatabase,
            'googleAccounts' => $googleAccounts,
            'success' => session('success'),
            'fail' => session('fail'),
        ]);
    }

    public function googleCreate()
    {
        try{
            $connections = $this->getConnection();
            DB::setDefaultConnection($connections);
            return inertia('Connection/GoogleCreate', [
                'currentDatabase' => $connections
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->route('google.show', ['connections' => $connections])
                ->with('fail', 'Failed to create connection. Please check your database credentials.');
        }

    }

    public function googleStore(StoreGoogleAdsAccountRequest $request)
    {
        $connections = $this->getConnection();
        DB::setDefaultConnection($connections);

        try {
            $validated = $request->validated();

            $existingAccount = GoogleAdsAccount::where('account_id', $validated['account_id'])->first();
            if ($existingAccount) {
                return redirect()->route('google.show', ['connections' => $connections])
                    ->with('fail', 'Account ID already exists. Please use a different Account ID.');
            }

            $account = new GoogleAdsAccount($validated);
            $account->user_id = auth()->id();
            $account->save();

            $this->createTableIfNotExists('campaigns', $account->account_id, $connections);
            $this->createTableIfNotExists('adgroups', $account->account_id, $connections);
            $this->createTableIfNotExists('ads', $account->account_id, $connections);
            $this->createTableIfNotExists('keywords', $account->account_id, $connections);

            return redirect()->route('google.show', ['connections' => $connections])
                ->with('success', 'Google Ads account created successfully.');
        } catch (\Exception $e) {
            return redirect()->route('google.show', ['connections' => $connections])
                ->with('fail', 'Failed to create Google Ads account: ' . $e->getMessage());
        }
    }

    private function createTableIfNotExists($tableType, $accountId, $connections)
    {
        $tableName = "{$connections}_google_adwords_{$tableType}";

        if (!Schema::connection($connections)->hasTable($tableName)) {
            $this->googleAdsReportService->createTable($tableType, $accountId, $connections);
        }
    }

    public function googleDestroy($account_id)
    {
        $connections = $this->getConnection();
        DB::setDefaultConnection($connections);

        try {
            // 確保使用正確的連接
            $account = GoogleAdsAccount::on($connections)->findOrFail($account_id);
            $name = $account->account_name;

            // 刪除帳戶
            $account->delete();

            \Log::info("Deleted GoogleAdsAccount with account_id: {$account_id}");

            return to_route('google.show', ['connections' => $connections]) -> with('success', "Google Ads account '{$name}' was deleted successfully.");
        } catch (\Exception $e) {
            \Log::error("Failed to delete Google Ads account {$account_id}: " . $e->getMessage());

            return to_route('google.show', ['connections' => $connections]) -> with('fail', 'Failed to delete Google Ads account: ' . $e->getMessage());
        }
    }

//google end

//meta start
    public function metaShow()
    {
        $connections = $this->getConnection();
        DB::setDefaultConnection($connections);

        $currentDatabase = DB::getDatabaseName();
        $metaAccounts = DB::table('meta_ads_accounts')->get(['account_id', 'account_name', 'created_at']);

        return inertia('Connection/MetaShow', [
            'connections' => $connections,
            'currentDatabase' => $currentDatabase,
            'metaAccounts' => $metaAccounts,
            'success' => session('success'),
            'fail' => session('fail'),
        ]);
    }

    public function metaCreate()
    {
        try{
            $connections = $this->getConnection();
            DB::setDefaultConnection($connections);
            return inertia('Connection/MetaCreate', [
                'currentDatabase' => $connections
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->route('meta.show', ['connections' => $connections])
                ->with('fail', 'Failed to create connection. Please check your database credentials.');
        }
    }

    public function metaStore(StoreMetaAdsAccountRequest $request)
    {
        $connections = $this->getConnection();
        DB::setDefaultConnection($connections);

        try {
            $validated = $request->validated();
            $existingAccount = MetaadsAccount::where('account_id', $validated['account_id'])->first();
            if ($existingAccount) {
                return redirect()->route('meta.show', ['connections' => $connections])
                    ->with('fail', 'Account ID already exists. Please use a different Account ID.');
            }

            $account = new MetaAdsAccount($validated);
            $account->user_id = auth()->id();
            $account->save();

            // 创建必要的表
            $this->metaAdsReportService->createTableIfNotExists('campaigns');
            $this->metaAdsReportService->createTableIfNotExists('adsets');
            $this->metaAdsReportService->createTableIfNotExists('ads');

            return redirect()->route('meta.show', ['connections' => $connections])
                ->with('success', 'Meta Ads account created successfully.');
        } catch (\Exception $e) {
            return redirect()->route('meta.show', ['connections' => $connections])
                ->with('fail', 'Failed to create Meta Ads account: ' . $e->getMessage());
        }
    }

    public function metaDestroy($account_id)
    {
        $connections = $this->getConnection();
        DB::setDefaultConnection($connections);

        try {
            $account = MetaAdsAccount::on($connections)->findOrFail($account_id);
            $account_name = $account->account_name;

            $account->delete();

            \Log::info("Deleted MetaAccount with account_id: {$account_id}");

            return to_route('meta.show', ['connections' => $connections])
                ->with('success', "Meta account '{$account_name}' was deleted successfully.");
        } catch (\Exception $e) {
            \Log::error("Failed to delete Meta account {$account_id}: " . $e->getMessage());

            return to_route('meta.show', ['connections' => $connections])
                ->with('fail', 'Failed to delete Meta account: ' . $e->getMessage());
        }
    }
//meta end

    public function sftpCreate()
    {
        return inertia('Connection/SftpCreate');
    }

}
