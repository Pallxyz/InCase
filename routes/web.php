<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing.index')->name('home');

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::view('/items', 'items.index')->middleware(['auth', 'verified'])->name('items.index');
Route::view('/schedule', 'schedules.index')->middleware(['auth', 'verified'])->name('schedule.index');
Route::view('/scan-history', 'scan-history.index')->middleware(['auth', 'verified'])->name('scan-history.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';