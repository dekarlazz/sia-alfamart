<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReportController;

// Halaman Depan langsung ke Dashboard
Route::get('/', [TransactionController::class, 'index'])->name('dashboard');

// CRUD Transaksi (Simpan, Update, Hapus)
Route::resource('transactions', TransactionController::class);

// Laporan
Route::get('/jurnal', [ReportController::class, 'jurnal'])->name('laporan.jurnal');
Route::get('/buku-besar', [ReportController::class, 'bukuBesar'])->name('laporan.buku_besar');
Route::get('/keuangan', [ReportController::class, 'keuangan'])->name('laporan.keuangan');