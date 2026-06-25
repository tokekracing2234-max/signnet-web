<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\ModelSyncController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/sync-model', [ModelSyncController::class, 'receiveModel']);
Route::post('/receive-model', [ModelSyncController::class, 'receiveModel']);