<?php

use Illuminate\Support\Facades\Route;
use Barryvdh\DomPDF\Facade\Pdf;

// Route root - redirect otomatis berdasarkan status login
Route::get('/', function () {
    // Jika user sudah login, redirect ke dashboard Filament
    if (auth()->check()) {
        return redirect('/gs-pettycash');
    }
    // Jika belum login, redirect ke halaman login Filament
    return redirect('/gs-pettycash/login');
});

// Route login - redirect ke Filament login
Route::get('/login', function () {
    return redirect('/gs-pettycash/login');
})->name('login');

// Route untuk PDF Laporan Mutasi Kas - Langsung generate PDF
Route::get('/laporan/mutasi-kas/pdf', [App\Http\Controllers\LaporanMutasiKasPdfController::class, 'generatePdf'])
    ->name('laporan.mutasi.kas.pdf')
    ->middleware('auth');

// Route untuk export proyeksi individual
Route::get('/proyeksi/export/{id}', [App\Http\Controllers\ProyeksiExportController::class, 'export'])
    ->name('proyeksi.export')
    ->middleware('auth');

// Route untuk export pengajuan budget individual
Route::get('/pengajuan-budget/export/{id}', [App\Http\Controllers\PengajuanBudgetExportController::class, 'export'])
    ->name('pengajuan-budget.export')
    ->middleware('auth');

// Route untuk export summary pengajuan budget individual
Route::get('/summary-pengajuan-budget/export/{id}', [App\Http\Controllers\SummaryPengajuanBudgetExportController::class, 'export'])
    ->name('summary-pengajuan-budget.export')
    ->middleware('auth');
