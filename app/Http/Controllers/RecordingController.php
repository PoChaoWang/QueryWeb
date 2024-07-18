<?php

namespace App\Http\Controllers;

use App\Models\Query;
use App\Models\Recording;
use App\Models\Outputting;
use App\Services\GoogleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class RecordingController extends Controller
{
    protected $googleService;

    public function __construct(GoogleService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function recordQueryExecution(Request $request, $queryId)
    {
        $connection = session('db_connection', config('database.default'));
        DB::setDefaultConnection($connection);

        Log::info('Using database connection:', ['connection' => DB::getDefaultConnection()]);
        Log::info('Received query ID:', ['queryId' => $queryId]);

        try {
            $query = Query::findOrFail($queryId);
            Log::info('Executing query for recording:', ['query' => $query->query_sql]);

            $result = DB::select($query->query_sql);
            Log::info('Query executed successfully.');

            $csvFilePath = $this->generateCsvFile($query->id, $result);

            $recording = new Recording();
            $recording->query_id = $query->id;
            $recording->query_sql = $query->query_sql;
            $recording->csv_file_path = $csvFilePath;
            $recording->updated_by = Auth::id();
            $recording->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $recording->status = 'success';
            $recording->save();

            Log::info('Recording saved successfully.');

            // 檢查是否需要輸出到 Google Sheets
            $this->outputToGoogleSheets($query, $recording);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error executing query for recording:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            $recording = new Recording();
            $recording->query_id = $queryId;
            $recording->query_sql = 'N/A';
            $recording->updated_by = Auth::id();
            $recording->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $recording->status = 'fail';
            $recording->fail_reason = $e->getMessage();
            $recording->save();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function generateCsvFile($queryId, $result)
    {
        $csvDir = storage_path("app/public/csv");

        if (!file_exists($csvDir)) {
            mkdir($csvDir, 0777, true);
        }

        $csvFileName = "query_{$queryId}_" . now()->format('Ymd_His') . '.csv';
        $csvFilePath = "{$csvDir}/{$csvFileName}";

        $file = fopen($csvFilePath, 'w');

        if (!empty($result)) {
            fputcsv($file, array_keys((array)$result[0]));
        }

        foreach ($result as $row) {
            fputcsv($file, (array)$row);
        }

        fclose($file);

        return $csvFileName;
    }

    public function outputToGoogleSheets(Query $query, Recording $recording)
    {
        $connection = $this->getConnection();

        // 切換到動態資料庫連接
        DB::setDefaultConnection($connection);

        // 確保獲取的是最新的 Outputting 資料
        $outputtings = Outputting::where('query_id', $recording->query_id)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->fresh();

        if ($outputtings->isEmpty()) {
            return;
        }

        $csvFilePath = storage_path("app/public/csv/{$recording->csv_file_path}");
        $csvData = array_map('str_getcsv', file($csvFilePath));

        foreach ($outputtings as $outputting) {
            $range = $outputting->sheet_name . '!A1';

            // 如果 append 为 true，则获取最后一行行号
            if ($outputting->append) {
                $lastRow = $this->googleService->getLastRow($outputting->sheet_id, $outputting->sheet_name);
                $range = $outputting->sheet_name . '!A' . $lastRow; // 插入到最後一行
            }

            Log::info('Attempting to write to Google Sheets', [
                'spreadsheet_id' => $outputting->sheet_id,
                'sheet_name' => $outputting->sheet_name,
                'range' => $range,
                'append' => $outputting->append
            ]);

            try {
                $result = $this->googleService->writeSheet(
                    $outputting->sheet_id,
                    $range,
                    $csvData,
                    $outputting->append
                );
                Log::info('Google Sheets API Response', ['response' => $result]);
            } catch (\Exception $e) {
                Log::error('Failed to write to Google Sheets', ['error' => $e->getMessage()]);
            }
        }
    }

    private function getConnection()
    {
        return session('db_connection', config('database.default'));
    }
}
