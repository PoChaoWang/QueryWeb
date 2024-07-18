<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Table extends Model
{
    protected $guarded = [];

    public static function getAllTables()
    {
        $databaseName = env('DB_DATABASE');
        $tables = DB::select("SHOW TABLES WHERE Tables_in_{$databaseName} LIKE '{$databaseName}%'");
        $tableNameKey = 'Tables_in_' . $databaseName;

        return array_map(function($table) use ($tableNameKey) {
            return $table->$tableNameKey;
        }, $tables);
    }


    public static function getTablesForClient($clientId)
    {
        return DB::select("SHOW TABLES LIKE 'client_{$clientId}_%'");
    }

    public static function createTable($tableName, $columns)
    {
        $columnsSql = implode(", ", array_map(function ($column) {
            return "{$column['name']} {$column['type']}";
        }, $columns));

        return DB::statement("CREATE TABLE {$tableName} ({$columnsSql})");
    }

    public static function queryTable($query)
    {
        if (self::isSafeQuery($query)) {
            return DB::select($query);
        }
        throw new \Exception("Unsafe query detected");
    }

    public static function updateTable($tableName, $columns)
    {
        $columnsSql = implode(", ", array_map(function ($column) {
            return "MODIFY COLUMN {$column['name']} {$column['type']}";
        }, $columns));

        return DB::statement("ALTER TABLE {$tableName} {$columnsSql}");
    }

    public static function getTableFields($tableName, $excludedFields = [])
    {
        $fields = DB::select("DESCRIBE {$tableName}");

        // 過濾掉指定的字段
        if (!empty($excludedFields)) {
            $fields = array_filter($fields, function ($field) use ($excludedFields) {
                return !in_array($field->Field, $excludedFields);
            });
        }

        return array_values($fields); // 確保返回的數組是索引數組
    }

    private static function isSafeQuery($query)
    {
        $unsafePatterns = [
            '/\b(ALTER|DROP|TRUNCATE|GRANT|REVOKE|CREATE)\b/i',
        ];

        foreach ($unsafePatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return false;
            }
        }
        return true;
    }
}
