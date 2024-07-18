<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQueryRequest;
use App\Http\Requests\UpdateQueryRequest;
use App\Http\Resources\OutputtingResource;
use App\Http\Resources\QueryResource;
use App\Http\Resources\RecordingResource;
use App\Http\Resources\ScheduleResource;
use App\Models\Query;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class QueryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->get('current_connection')==null) {
            return redirect()->route('client.index');
        }

        $name = $request->query('name');
        $createdBy = $request->query('created_by');
        $updatedBy = $request->query('updated_by');

        $queries = Query::when($name, function ($query, $name) {
            return $query->where('name', 'like', '%' . $name . '%');
        })
        ->when($createdBy, function ($query, $createdBy) {
            return $query->whereHas('createdBy', function ($q) use ($createdBy) {
                $q->where('name', $createdBy);
            });
        })
        ->when($updatedBy, function ($query, $updatedBy) {
            return $query->whereHas('updatedBy', function ($q) use ($updatedBy) {
                $q->where('name', $updatedBy);
            });
        })
        ->orderBy('updated_at', 'desc')
        ->paginate(10)
        ->onEachSide(1);

        return inertia('Query/Index', [
            'queries' => QueryResource::collection($queries),
            'queryParams' => $request->query() ?: null,
            'currentDatabase' => $request->get('current_connection'), // 從請求中獲取當前資料庫連接
            'success' => session('success'),
            'fail' => session('fail'),

        ]);
    }

    private function getTableDetails()
    {
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();
        $tablePrefix = 'Tables_in_' . $databaseName;
        $tableNames = array_map(function($table) use ($tablePrefix) {
            return $table->$tablePrefix;
        }, $tables);

        $excludedColumns = ['client_id', 'created_at', 'updated_at'];

        $tableDetails = [];
        foreach ($tableNames as $table) {
            // 排除不以 databaseName 開頭的表
            if (strpos($table, $databaseName) !== 0) {
                continue;
            }

            $columns = DB::select("SHOW COLUMNS FROM {$table}");
            $columnDetails = array_map(function($column) use ($excludedColumns) {
                if (in_array($column->Field, $excludedColumns)) {
                    return null;
                }
                return [
                    'name' => $column->Field,
                    'type' => $column->Type,
                ];
            }, $columns);

            // 過濾掉 null 值
            $columnDetails = array_filter($columnDetails);

            $tableDetails[$table] = $columnDetails;
        }

        return $tableDetails;
    }

    public function create()
    {
        $tableDetails = $this->getTableDetails();

        if (empty($tableDetails)) {
            return redirect()->route('query.index')
                ->with('fail', 'No tables found in the selected database.');
        }

        return inertia('Query/Create', [
            'tables' => $tableDetails,
        ]);
    }

    public function verify(Request $request)
    {
        $querySql = $request->input('query_sql');

        // 確保 querySql 是字符串類型
        if (!is_string($querySql)) {
            throw new \InvalidArgumentException('Invalid SQL query');
        }
        if (!preg_match('/^\s*SELECT/i', $querySql)) {
            throw new \InvalidArgumentException('Only SELECT queries are allowed');
        }

        try {
            $result = DB::select($querySql);
            return response()->json(['result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    protected $recordingController;

    public function __construct(RecordingController $recordingController)
    {
        $this->recordingController = $recordingController;
    }

    // public function execute(Request $request, $id)
    // {
    //     try {
    //         $query = Query::findOrFail($id);
    //         $this->recordingController->recordQueryExecution($query);
    //     } catch (\Exception $e) {
    //         \Log::error('Error executing query:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    //         return to_route('query.show', ['id' => $query->id])->with('fail', 'Error executing the query.');
    //     }
    //     return to_route('query.show', ['id' => $query->id])->with('success', 'Query was executed again');
    // }

    public function store(StoreQueryRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        try{
            $query = new Query();
            $query->fill($data)->save();
            $this->recordingController->recordQueryExecution(new Request(), $query->id);
        } catch (\Exception $e){
            \Log::error('Error creating query:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return to_route('query.index')->with('fail', 'Error creating the query.');
        }
        return to_route('query.show', ['id' => $query->id])->with('success', 'Query was created');
    }

    public function show($id)
    {
        $query = Query::findOrFail($id);
        $recordings = $query->recordings()->orderBy('updated_at', 'desc')->paginate(10)->onEachSide(1);
        $outputtings = $query->outputtings()->orderBy('updated_at', 'desc')->get();
        $schedules = $query->schedules()->orderBy('updated_at', 'desc')->get();
        return inertia('Query/Show', [
            'query' => new QueryResource($query),
            'recordings' => RecordingResource::collection($recordings),
            'outputtings' => OutputtingResource::collection($outputtings),
            'schedules' => ScheduleResource::collection($schedules),
            'currentDatabase' => request()->get('current_connection'),
            'success' => session('success'),
            'fail' => session('fail'),
        ]);
    }

    public function edit($id)
    {
        $query = Query::findOrFail($id);
        $tableDetails = $this->getTableDetails();

        return inertia('Query/Edit', [
            'tables' => $tableDetails,
            'query' => new QueryResource($query),
        ]);
    }

    public function update(UpdateQueryRequest $request, $id)
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        try {
            $query = Query::findOrFail($id);
            $query->update($data);
            $query->refresh();
            $this->recordingController->recordQueryExecution(new Request(), $query->id);
        } catch (\Exception $e) {
            \Log::error('Error updating query:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return to_route('query.show', ['id' => $id])->with('fail', 'Error updating the query.');
        }

        return to_route('query.show', ['id' => $query->id])->with('success', 'Query was updated');
    }

    public function destroy($id)
    {
        \Log::info('Query start to delete', ['query'=> $id]);
        $query = Query::findOrFail($id);
        $name = $query->name;
        $query->delete();
        return to_route('query.index')->with('success', "Query \" $name \" was deleted");
    }
}
