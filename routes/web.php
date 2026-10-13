<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistribusiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LimbahDapurController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\SampahKemasanController;
use App\Http\Controllers\SisaPanganController;
use App\Http\Controllers\VerifikasiController;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard utama
Route::get('/', function () {
    return redirect()->route('dashboard.index');
});

// 1. Dashboard & API Grafik
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/api/chart/tren-harian', [DashboardController::class, 'chartTrenHarian'])->name('chart.tren-harian');
Route::get('/api/chart/ranking-sppg', [DashboardController::class, 'chartRankingSppg'])->name('chart.ranking-sppg');
Route::get('/api/chart/komposisi-kemasan', [DashboardController::class, 'chartKomposisiKemasan'])->name('chart.komposisi-kemasan');
Route::get('/admin/monitoring/sisa-pangan', [DashboardController::class, 'monitoringSisaPangan'])->name('admin.monitoring.sisa-pangan');
Route::get('/admin/monitoring/kemasan', [DashboardController::class, 'monitoringKemasan'])->name('admin.monitoring.kemasan');

// 2. Portal SPPG (Produksi, Distribusi, Limbah Dapur, Riwayat)
Route::prefix('sppg')->name('sppg.')->group(function () {
    Route::get('/riwayat', [ProduksiController::class, 'riwayat'])->name('riwayat');
    Route::resource('produksi', ProduksiController::class);
    Route::resource('distribusi', DistribusiController::class);
    Route::resource('limbah-dapur', LimbahDapurController::class);
});

// 3. Portal Sekolah (Penerimaan, Sisa Pangan, Sampah Kemasan, Riwayat)
Route::prefix('sekolah')->name('sekolah.')->group(function () {
    Route::get('/riwayat', [PenerimaanController::class, 'riwayat'])->name('riwayat');
    Route::resource('penerimaan', PenerimaanController::class);
    Route::resource('sisa-pangan', SisaPanganController::class);
    Route::resource('sampah-kemasan', SampahKemasanController::class);
});

// 4. Portal Admin (Verifikasi, Laporan, Master Data)
Route::prefix('admin')->name('admin.')->group(function () {
    // Verifikasi
    Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
    Route::get('/verifikasi/{id}', [VerifikasiController::class, 'show'])->name('verifikasi.show');
    Route::put('/verifikasi/{id}', [VerifikasiController::class, 'update'])->name('verifikasi.update');

    // Laporan & Ekspor
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');

    // Master Data (SPPG, Sekolah, Menu)
    Route::get('/master/{type?}', [MasterDataController::class, 'index'])->name('master.index');
    Route::post('/master/{type?}', [MasterDataController::class, 'store'])->name('master.store');
    Route::put('/master/{type}/{id}', [MasterDataController::class, 'update'])->name('master.update');
    Route::delete('/master/{type}/{id}', [MasterDataController::class, 'destroy'])->name('master.destroy');
});
