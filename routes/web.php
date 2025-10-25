<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RiskControlController;
use App\Http\Controllers\MahRegisterController;
use App\Http\Controllers\BarrierAssessmentController;
use Illuminate\Support\Facades\Route;

// Biarkan route 'Auth' di luar. 
// Ini agar halaman login bisa diakses oleh semua orang.
Auth::routes(['register' => false]); // Menonaktifkan pendaftaran


// --- GRUP UNTUK HALAMAN YANG WAJIB LOGIN ---
Route::middleware(['auth'])->group(function () {

    // INI YANG DIPERBAIKI:
    // Pastikan KEDUA rute ini mengarah ke DashboardController
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [DashboardController::class, 'index'])->name('home');

    // Rute CRUD MAH Register
    Route::resource('mah-register', MahRegisterController::class);

    // Rute CRUD Risk Control
    Route::resource('risk-control', RiskControlController::class);

    Route::resource('barrier-assessments', BarrierAssessmentController::class);
});
