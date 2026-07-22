<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Teacher\SubjectController;

use App\Http\Controllers\Student\ItemController;
use App\Http\Controllers\Student\ScanHistoryController;
use App\Http\Controllers\Student\ScheduleController;

Route::view('/', 'landing.index')->name('home');

Route::middleware('auth')->group(function () {


    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');


    Route::middleware('role:student')->group(function () {

        Route::resource('items', ItemController::class);

        Route::get('/scan-history', [ScanHistoryController::class, 'index'])
            ->name('scan-history.index');

        Route::get('/schedule', [ScheduleController::class, 'index'])
            ->name('schedule.index');
    });

    Route::middleware('role:teacher')->group(function () {

        Route::resource('subjects', SubjectController::class);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';
