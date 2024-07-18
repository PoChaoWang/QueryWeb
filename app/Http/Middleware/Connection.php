<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class Connection
{
    public function handle(Request $request, Closure $next)
    {
        if (Session::has('client_connection')) {
            $connection = Session::get('client_connection');
            DB::setDefaultConnection($connection);
            Log::info('Current database connection:', ['connection' => DB::getDefaultConnection()]);
            $request->attributes->set('current_connection', $connection);
        }else {
            // Set default connection if none is provided
            DB::setDefaultConnection(config('database.default'));
        }

        return $next($request);
    }
}
