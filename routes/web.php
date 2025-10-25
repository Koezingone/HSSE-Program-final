<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RiskControlController;
use App\Http\Controllers\MahRegisterController;
use App\Http\Controllers\BarrierAssessmentController;
use Illuminate\Support\Facades\Route;

// Biarkan route 'Auth' di luar. 
Auth::routes(['register' => false]); // Menonaktifkan pendaftaran


// --- GRUP UNTUK HALAMAN YANG WAJIB LOGIN ---
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [DashboardController::class, 'index'])->name('home');

    // --- BAGIAN MAH REGISTER ---
    // Rute PDF HARUS di atas Resource
    Route::get('/mah-register/print-pdf', [MahRegisterController::class, 'printPdf'])
        ->name('mah-register.printPdf');

    // Baru daftarkan resource-nya
    Route::resource('mah-register', MahRegisterController::class);
    // --- AKHIR BAGIAN MAH REGISTER ---


    // Rute CRUD Risk Control
    Route::resource('risk-control', RiskControlController::class);

    // Rute CRUD Barrier Assessment
    Route::resource('barrier-assessments', BarrierAssessmentController::class);
});
