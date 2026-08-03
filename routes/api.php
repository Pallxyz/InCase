<?php

use App\Http\Controllers\Api\ScanController;
use Illuminate\Support\Facades\Route;

Route::post('/scan', [ScanController::class, 'store'])->middleware('api.key');
