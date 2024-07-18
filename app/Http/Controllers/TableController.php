<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTableRequest;
use App\Http\Requests\UpdateTableRequest;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tables = Table::getAllTables();
        $tableData = [];
        $excludedFields = ['client_id', 'created_at', 'updated_at']; // 假設你要排除這些字段

        foreach ($tables as $tableName) {
            $fields = Table::getTableFields($tableName, $excludedFields);
            $tableData[] = [
                'name' => $tableName,
                'fields' => $fields
            ];
        }

        return inertia('Table/Index', [
            'tables' => $tableData,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Table/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTableRequest $request)
    {
        $request->validate([
            'table_name' => 'required|string',
            'columns' => 'required|array',
            'columns.*.name' => 'required|string',
            'columns.*.type' => 'required|string',
        ]);

        Table::createTable($request->table_name, $request->columns);
        return redirect()->route('tables.index')->with('success', 'Table created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Table $tabel)
    {
        $clientId = request()->query('client_id');
        $tables = Table::getAllTables();
        $tableData = [];
        $excludedFields = ['client_id', 'created_at', 'updated_at']; // 假設你要排除這些字段

        foreach ($tables as $tableName) {
            $fields = Table::getTableFields($tableName, $excludedFields);
            $tableData[] = [
                'name' => $tableName,
                'fields' => $fields
            ];
        }

        return inertia('Table/Index', [
            'tables' => $tableData,
            'client_id' => $clientId
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Table $tabel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTableRequest $request, $tableName)
    {
        $request->validate([
            'columns' => 'required|array',
            'columns.*.name' => 'required|string',
            'columns.*.type' => 'required|string',
        ]);

        Table::updateTable($tableName, $request->columns);
        return redirect()->route('tables.index')->with('success', 'Table updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Table $tabel)
    {
        //
    }

    public function queryTable(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $results = Table::queryTable($request->query);
        return inertia('Table/Query', [
            'results' => $results,
        ]);
    }

    public function getTablesForClient($clientId)
    {
        $tables = Table::getTablesForClient($clientId);
        $tableData = [];
        $excludedFields = ['id', 'password']; // 假設你要排除這些字段

        foreach ($tables as $tableName) {
            $fields = Table::getTableFields($tableName, $excludedFields);
            $tableData[] = [
                'name' => $tableName,
                'fields' => $fields
            ];
        }
        return response()->json($tableData);
    }

    public function getAllTablesWithFields()
    {
        $tables = Table::getAllTables();
        $tableData = [];
        $excludedFields = ['id', 'password']; // 假設你要排除這些字段

        foreach ($tables as $tableName) {
            $fields = Table::getTableFields($tableName, $excludedFields);
            $tableData[] = [
                'name' => $tableName,
                'fields' => $fields
            ];
        }

        return response()->json($tableData);
    }
}
