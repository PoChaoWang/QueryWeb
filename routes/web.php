<?php

use App\Http\Controllers\ClientDataController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\OrchestratorController;
use App\Http\Controllers\OutputtingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueryController;
use App\Http\Controllers\RecordingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');

    Route::get('/clients', [ClientDataController::class, 'index'])->name('client.index');
    Route::get('/clients/create', [ClientDataController::class, 'create'])->name('client.create');
    Route::post('/clients', [ClientDataController::class, 'store'])->name('client.store');
    Route::delete('/clients/{client}', [ClientDataController::class, 'destroy'])->name('clients.destroy');
    Route::post('/set-connection', [ClientDataController::class, 'setConnection'])->name('set-connection');
    Route::get('/connections', [ConnectionController::class, 'index'])->name('connection.index');
    Route::get('/orchestrator', [OrchestratorController::class, 'index'])->name('orchestrator.index');

    Route::middleware(['connection'])->group(function () {
        Route::get('/clients/{connections}/queries', [ClientDataController::class, 'show'])->name('clients.show'); // go to query.index

        // Queries
        Route::get('/queries', [QueryController::class, 'index'])->name('query.index');
        Route::get('/queries/create', [QueryController::class, 'create'])->name('query.create');
        Route::post('/queries/store', [QueryController::class, 'store'])->name('query.store');
        Route::get('/queries/{id}', [QueryController::class, 'show'])->name('query.show');
        Route::get('/queries/{id}/edit', [QueryController::class, 'edit'])->name('query.edit');
        Route::put('/queries/{id}', [QueryController::class, 'update'])->name('query.update');
        Route::delete('/queries/{id}', [QueryController::class, 'destroy'])->name('query.destroy');
        Route::post('/query/verify', [QueryController::class, 'verify'])->name('query.verify');

        Route::post('/recordings/execute/{query}', [RecordingController::class, 'recordQueryExecution'])->name('recording-execution');

        Route::post('/outputting/store/{query}', [OutputtingController::class, 'store'])->name('outputting.store');
        Route::put('/outputting/update/{outputting}', [OutputtingController::class, 'update'])->name('outputting.update');
        Route::delete('/outputting/destroy/{outputting}', [OutputtingController::class, 'destroy'])->name('outputting.destroy');

        Route::get('/connections/google_create', [ConnectionController::class, 'googleCreate'])->name('google.create');
        Route::post('/connections/google', [ConnectionController::class, 'googleStore'])->name('google.store');
        Route::get('/connections/{connections}/google', [ConnectionController::class, 'googleShow'])->name('google.show');
        Route::delete('/connections/google/delete/{account_id}', [ConnectionController::class, 'googleDestroy'])->name('google.destroy');


        Route::get('/connections/meta_create', [ConnectionController::class, 'metaCreate'])->name('meta.create');
        Route::post('/connections/meta', [ConnectionController::class, 'metaStore'])->name('meta.store');
        Route::get('/connections/{connections}/meta', [ConnectionController::class, 'metaShow'])->name('meta.show');
        Route::delete('/connections/meta/delete/{account_id}', [ConnectionController::class, 'metaDestroy'])->name('meta.destroy');

        Route::get('/connections/sftp_create', [ConnectionController::class, 'sftpCreate'])->name('sftp.create');

        Route::get('/orchestrator/google/edit', [OrchestratorController::class, 'googleEdit'])->name('google.edit');
        Route::post('/orchestrators/google/update', [OrchestratorController::class, 'googleUpdate'])->name('google.update');

        Route::get('/orchestrator/meta/edit', [OrchestratorController::class, 'metaEdit'])->name('meta.edit');
        Route::post('/orchestrators/meta/update', [OrchestratorController::class, 'metaUpdate'])->name('meta.update');

    });

});

// 不需要資料庫連接的路由
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

