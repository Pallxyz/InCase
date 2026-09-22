<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemScanPollController;
use App\Http\Controllers\Teacher\AcademicYearController;

use App\Http\Controllers\Teacher\SubjectController;
use App\Http\Controllers\Teacher\RoomChangeController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\HolidayController;

use App\Http\Controllers\Student\ItemController;
use App\Http\Controllers\Student\ItemResolutionController;
use App\Http\Controllers\Student\ScanHistoryController;
use App\Http\Controllers\Student\ScheduleController;

Route::view('/', 'landing.index')->name('home');

Route::middleware('auth')->group(function () {

    Route::post('/push/subscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/items/scan-poll', [ItemScanPollController::class, 'show'])->name('items.scan-poll');

    Route::middleware('role:student')->group(function () {

        Route::resource('items', ItemController::class);

    // Barang belum kembali saat cek pulang: dikumpulkan / hilang (butuh confirmed=1)
    Route::post('/items/{item}/resolve', [ItemResolutionController::class, 'store'])
        ->name('items.resolve');

        Route::get('/scan-history', [ScanHistoryController::class, 'index'])
            ->name('scan-history.index');

        Route::get('/schedule', [ScheduleController::class, 'index'])
            ->name('schedule.index');
    });

    // ADMIN: kelola tahun ajaran & hari libur sekolah
    Route::middleware('role:admin')->group(function () {

        Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
        Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
        Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');

        Route::resource('academic-years', AcademicYearController::class)
            ->only(['index', 'store', 'destroy']);

        Route::post('academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate'])
            ->name('academic-years.activate');

        Route::post('academic-years/{academicYear}/copy-schedules', [AcademicYearController::class, 'copySchedules'])
            ->name('academic-years.copy-schedules');

        Route::get('teachers', [TeacherController::class, 'index'])->name('teachers.index');
        Route::post('teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::delete('teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

        Route::get('academic-years/{academicYear}/export', [AcademicYearController::class, 'export'])
            ->name('academic-years.export');
    });

    // GURU: kelola jadwal, barang wajib, dan PR miliknya sendiri
    Route::middleware('role:teacher')->group(function () {
        Route::resource('subjects', SubjectController::class);

        // Pindah ruang untuk satu tanggal (bukan permanen)
        Route::post('subjects/{subject}/room-changes', [RoomChangeController::class, 'store'])
            ->name('subjects.room-changes.store');
        Route::delete('subjects/{subject}/room-changes/{roomChange}', [RoomChangeController::class, 'destroy'])
            ->name('subjects.room-changes.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';