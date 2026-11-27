<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EtalaseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\PesananController;
use App\Http\Controllers\Admin\PelangganController;

// ==========================================
// SISI ETALASE PUBLIK (Anggota 1 - Ketua)
// ==========================================
Route::get('/', [EtalaseController::class, 'index'])->name('etalase.index');
Route::get('/katalog', [EtalaseController::class, 'index'])->name('etalase.katalog');

// Placeholder rute Keranjang & Checkout (dikerjakan di Minggu 2)
Route::post('/keranjang/tambah', [EtalaseController::class, 'tambahKeranjang'])->name('keranjang.tambah');
Route::post('/keranjang/hapus', [EtalaseController::class, 'hapusKeranjang'])->name('keranjang.hapus');
Route::post('/checkout', [EtalaseController::class, 'checkout'])->name('etalase.checkout');

// ==========================================
// PANEL ADMIN (Anggota 2 & Anggota 3)
// ==========================================
Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard Analitik (Anggota 3)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // CRUD Produk & Soal 3e (Anggota 2)
    Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
    Route::get('/produk/tambah', [ProdukController::class, 'create'])->name('produk.create');
    Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
    Route::get('/produk/{id}/ubah', [ProdukController::class, 'edit'])->name('produk.edit');
    Route::put('/produk/{id}', [ProdukController::class, 'update'])->name('produk.update');
    Route::delete('/produk/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy');

    // Pesanan, Detail, Ubah Status & Soal 3b, 3j (Anggota 2)
    Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan/{id}', [PesananController::class, 'show'])->name('pesanan.show');
    Route::patch('/pesanan/{id}/status', [PesananController::class, 'updateStatus'])->name('pesanan.status');

    // Pelanggan & Soal 3d, 3f, 3i (Anggota 2)
    Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');
    Route::post('/pelanggan', [PelangganController::class, 'store'])->name('pelanggan.store');
    Route::put('/pelanggan/{id}', [PelangganController::class, 'update'])->name('pelanggan.update');
    Route::delete('/pelanggan/{id}', [PelangganController::class, 'destroy'])->name('pelanggan.destroy');

    Route::patch('/produk/{id}/toggle-status', [ProdukController::class, 'toggleStatus'])->name('produk.toggle');
    
    });