<?php

namespace App\Http\Controllers;

use App\Http\Resources\QueryResource;
use App\Models\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use PDO;

class ClientDataController extends Controller
{
    public function setConnection(Request $request)
    {
        $client = $request->input('client');

        // 存儲連接名稱在 session 中
        Session::put('client_connection', $client);

        return response()->json(['message' => 'Connection set successfully']);
    }

    public function index()
    {
        // 設置默認資料庫連接
        DB::setDefaultConnection('data_studio');

        // 获取所有连接名称
        $connections = DB::connection('data_studio')->table('client_connections')->pluck('connection_name')->toArray();
        $selectedConnection = $this->getConnection();
        $tables = $this->getTables($selectedConnection);

        return inertia('Client/Index', [
            'connections' => $connections,
            'tables' => $tables,
            'connection' => $selectedConnection,
            'success' => session('success'),
            'fail' => session('fail'),
        ]);
    }

    // 获取当前连接
    private function getConnection()
    {
        return session('db_connection', config('database.default'));
    }

    // 获取指定连接的表
    private function getTables($connection)
    {
        $tables = DB::connection($connection)->select('SHOW TABLES');
        $tableNames = array_map(function($table) use ($connection) {
            $tableName = 'Tables_in_' . DB::connection($connection)->getDatabaseName();
            return $table->$tableName;
        }, $tables);

        return $tableNames;
    }

    public function create()
    {
        return inertia('Client/Create');
    }

    public function show($connection)
    {

        $queryModel = new Query();
        $queryModel->setConnectionByClient($connection);
        $connection = $this->getConnection();
        $queries = $queryModel->orderBy('updated_at', 'desc')->paginate(10)->onEachSide(1);

        return inertia('Query/Index', [
            'queries' => QueryResource::collection($queries),
            'queryParams' => request()->query() ?: null,
            'currentDatabase' => $connection,
        ]);
    }

// 新增開始
    public function store(Request $request)
    {
        $newClient = $request->input('client_name');

        if (preg_match('/[^a-zA-Z0-9_]/', $newClient)) {
            return redirect()->route('client.index')
                ->with('fail', 'Client name contains invalid characters. Please use only letters, numbers, and underscores.');
        }

        $this->createDatabaseAndTables($newClient);
        $this->updateConfigFile($newClient);
        $this->updateEnvFile($newClient);
        $this->storeNewConnection($newClient);

        return to_route('client.index')
            ->with('success', 'New Client was created');
    }

    private function storeNewConnection($newClient)
    {
        DB::table('client_connections')->insert([
            'connection_name' => $newClient,
        ]);
    }

    private function createDatabaseAndTables($newClient)
    {
        $databaseName = str_replace('-', '_', $newClient);
        $charset = config('database.connections.mysql.charset', 'utf8mb4');
        $collation = config('database.connections.mysql.collation', 'utf8mb4_unicode_ci');

        try {
            // 创建数据库
            DB::statement("CREATE DATABASE $databaseName CHARACTER SET $charset COLLATE $collation");

            // 动态创建连接以使用新数据库
            Config::set("database.connections.$databaseName", array_merge(
                config('database.connections.mysql'),
                ['database' => $databaseName]
            ));

            // 使用新的连接来创建表格
            DB::connection($databaseName)->getSchemaBuilder()->create('queries', function ($table) {
                $table->id();
                $table->string('name');
                $table->text('query_sql');
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('updated_by');
                $table->timestamps();

                $table->foreign('created_by')->references('id')->on('data_studio.users');
                $table->foreign('updated_by')->references('id')->on('data_studio.users');
            });

            DB::connection($databaseName)->getSchemaBuilder()->create('outputtings', function ($table) {
                $table->id();
                $table->foreignId('query_id')->constrained('queries')->onDelete('cascade');
                $table->string('sheet_id');
                $table->string('sheet_name');
                $table->boolean('append');
                $table->timestamps();
            });

            DB::connection($databaseName)->getSchemaBuilder()->create('recordings', function ($table) {
                $table->id();
                $table->foreignId('query_id')->constrained('queries')->onDelete('cascade');
                $table->unsignedBigInteger('updated_by');
                $table->string('csv_file_path')->nullable();
                $table->string('query_sql');
                $table->string('status')->default('processing');
                $table->string('fail_reason')->nullable();
                $table->timestamps();

                $table->foreign('updated_by')->references('id')->on('data_studio.users');
            });

            DB::connection($databaseName)->getSchemaBuilder()->create('schedules', function ($table) {
                $table->id();
                $table->foreignId('query_id')->constrained('queries')->onDelete('cascade');
                $table->enum('week_day', ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])->nullable();
                $table->time('time');
                $table->timestamps();
            });

            DB::connection($databaseName)->getSchemaBuilder()->create('google_ads_accounts', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('account_id');
                $table->string('account_name');
                $table->timestamps();
            });

            DB::connection($databaseName)->getSchemaBuilder()->create('meta_ads_accounts', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('account_id');
                $table->string('account_name');
                $table->timestamps();
            });

        } catch (\Exception $e) {
            // 捕捉并记录错误
            \Log::error('Error creating database and tables: ' . $e->getMessage());
            throw $e; // 可选：重新抛出异常以便进一步处理
        }
    }

    private function updateConfigFile($newClient)
    {
        $configPath = config_path('database.php');
        $config = include($configPath);

        $newConnectionName = $newClient;
        $newConnection = [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $newConnectionName,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ];

        // 确保新的连接名不在现有的连接中
        if (!isset($config['connections'][$newConnectionName])) {
            $config['connections'][$newConnectionName] = $newConnection;

            $newConfigContent = "<?php\n\nreturn " . var_export($config, true) . ";\n\n";
            File::put($configPath, $newConfigContent);

            // 确认配置已更新
            \Log::info('Updated config file with new connection: ' . $newClient);
        }
    }

    private function updateEnvFile($newClient)
    {
        $envPath = base_path('.env');

        $sanitizedClient = str_replace('-', '_', strtoupper($newClient));

        $envContent = file_get_contents($envPath);
        $newEnvContent = $envContent
            . "\nDB_CONNECTION_" . $sanitizedClient . "=mysql"
            . "\nDB_HOST_" . $sanitizedClient . "=127.0.0.1"
            . "\nDB_PORT_" . $sanitizedClient . "=3306"
            . "\nDB_DATABASE_" . $sanitizedClient . "=$newClient"
            . "\nDB_USERNAME_" . $sanitizedClient . "=root"
            . "\nDB_PASSWORD_" . $sanitizedClient . "=\n";

        file_put_contents($envPath, $newEnvContent);

        \Log::info('Updated .env file with new connection: ' . $newClient);
    }
// 新增結束

// 刪除開始
    public function destroy(Request $request, $client)
    {
        $inputName = $request->input('confirmation_name');
        if ($inputName !== $client) {
            return redirect()->route('client.index')
                ->with('fail', 'Database name does not match.');
        }

        $this->removeDatabaseAndConfigurations($client);

        // 返回成功消息或其他处理方式
        return redirect()->route('client.index')
            ->with('success', 'Client database and configurations were deleted');
    }

    private function removeDatabaseAndConfigurations($client)
    {
        $connectionName = $client;
        $databaseName = $client;

        try {
            // 删除数据库
            DB::statement("DROP DATABASE $databaseName");

            // 从 client_connections 表中删除记录
            DB::table('client_connections')->where('connection_name', $connectionName)->delete();

            // 更新 database.php 配置文件
            $this->removeFromDatabaseConfig($connectionName);

            // 更新 .env 文件
            $this->removeFromEnvFile($client);

            // 确认删除
            \Log::info('Deleted database and configurations for client: ' . $client);

        } catch (\Exception $e) {
            // 处理错误
            \Log::error('Error deleting database and configurations for client ' . $client . ': ' . $e->getMessage());
            throw $e;
        }
    }

    private function removeFromDatabaseConfig($connectionName)
    {
        $configPath = config_path('database.php');
        $config = include($configPath);

        if (isset($config['connections'][$connectionName])) {
            unset($config['connections'][$connectionName]);

            $newConfigContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
            File::put($configPath, $newConfigContent);

            \Log::info('Updated database.php to remove connection: ' . $connectionName);
        }
    }

    private function removeFromEnvFile($client)
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        $sanitizedClient = str_replace('-', '_', strtoupper($client));

        $envContent = preg_replace(
            "/DB_CONNECTION_" . $sanitizedClient . "=.*\n/",
            '',
            $envContent
        );
        $envContent = preg_replace(
            "/DB_HOST_" . $sanitizedClient . "=.*\n/",
            '',
            $envContent
        );
        $envContent = preg_replace(
            "/DB_PORT_" . $sanitizedClient . "=.*\n/",
            '',
            $envContent
        );
        $envContent = preg_replace(
            "/DB_DATABASE_" . $sanitizedClient . "=.*\n/",
            '',
            $envContent
        );
        $envContent = preg_replace(
            "/DB_USERNAME_" . $sanitizedClient . "=.*\n/",
            '',
            $envContent
        );
        $envContent = preg_replace(
            "/DB_PASSWORD_" . $sanitizedClient . "=.*\n/",
            '',
            $envContent
        );

        file_put_contents($envPath, $envContent);

        \Log::info('Updated .env to remove configurations for client: ' . $client);
    }
// 刪除結束
}
