<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/produk', [App\Http\Controllers\ProductController::class, 'index'])->name('produk.index');
    Route::post('/produk', [App\Http\Controllers\ProductController::class, 'store'])->name('produk.store');
    Route::get('/produk/{product}/edit', [App\Http\Controllers\ProductController::class, 'edit'])->name('produk.edit');
    Route::put('/produk/{product}', [App\Http\Controllers\ProductController::class, 'update'])->name('produk.update');
    Route::delete('/produk/{product}', [App\Http\Controllers\ProductController::class, 'destroy'])->name('produk.destroy');

    Route::get('/pelanggan', [App\Http\Controllers\CustomerController::class, 'index'])->name('pelanggan.index');
    Route::post('/pelanggan', [App\Http\Controllers\CustomerController::class, 'store'])->name('pelanggan.store');
    Route::get('/pelanggan/{customer}/edit', [App\Http\Controllers\CustomerController::class, 'edit'])->name('pelanggan.edit');
    Route::put('/pelanggan/{customer}', [App\Http\Controllers\CustomerController::class, 'update'])->name('pelanggan.update');
    Route::delete('/pelanggan/{customer}', [App\Http\Controllers\CustomerController::class, 'destroy'])->name('pelanggan.destroy');

    Route::get('/transaksi', [App\Http\Controllers\TransactionController::class, 'index'])->name('transaksi.index');
    Route::post('/transaksi', [App\Http\Controllers\TransactionController::class, 'store'])->name('transaksi.store');
    Route::get('/transaksi/{transaction}', [App\Http\Controllers\TransactionController::class, 'show'])->name('transaksi.show');
    Route::patch('/transaksi/{transaction}/status', [App\Http\Controllers\TransactionController::class, 'updateStatus'])->name('transaksi.updateStatus');

    Route::get('/stok', [App\Http\Controllers\StockController::class, 'index'])->name('stok.index');
    Route::post('/stok', [App\Http\Controllers\StockController::class, 'store'])->name('stok.store');

    Route::get('/pengiriman', [App\Http\Controllers\DeliverySlotController::class, 'index'])->name('pengiriman.index');
    Route::post('/pengiriman', [App\Http\Controllers\DeliverySlotController::class, 'store'])->name('pengiriman.store');
    Route::get('/pengiriman/{deliverySlot}/edit', [App\Http\Controllers\DeliverySlotController::class, 'edit'])->name('pengiriman.edit');
    Route::put('/pengiriman/{deliverySlot}', [App\Http\Controllers\DeliverySlotController::class, 'update'])->name('pengiriman.update');

    Route::get('/preorder', [App\Http\Controllers\PreOrderController::class, 'index'])->name('preorder.index');
    Route::post('/preorder', [App\Http\Controllers\PreOrderController::class, 'store'])->name('preorder.store');
    Route::patch('/preorder/{preOrder}/alokasikan', [App\Http\Controllers\PreOrderController::class, 'alokasikan'])->name('preorder.alokasikan');
    Route::patch('/preorder/{preOrder}/batalkan', [App\Http\Controllers\PreOrderController::class, 'batalkan'])->name('preorder.batalkan');

    Route::middleware('owner')->group(function () {
        Route::get('/laporan', [App\Http\Controllers\ReportController::class, 'index'])->name('laporan.index');

        Route::get('/pengguna', [App\Http\Controllers\UserController::class, 'index'])->name('pengguna.index');
        Route::post('/pengguna', [App\Http\Controllers\UserController::class, 'store'])->name('pengguna.store');
        Route::get('/pengguna/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('pengguna.edit');
        Route::put('/pengguna/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('pengguna.update');
        Route::patch('/pengguna/{user}/toggle-active', [App\Http\Controllers\UserController::class, 'toggleActive'])->name('pengguna.toggleActive');
    });

    // ════════════════════════════════════════════════════════════════════
    // ⚠️ PLACEHOLDER SEMENTARA — dibutuhkan karena SIDEBAR memanggil route()
    // untuk semua menu ini di setiap halaman manapun (bukan hanya saat diklik).
    // WAJIB DIHAPUS satu per satu, PERSIS saat tahap aslinya dikerjakan:
    //   pengiriman.index  → hapus baris ini di Tahap 13 x
    //   preorder.index    → hapus baris ini di Tahap 14 x
    //   hutang.index      → hapus baris ini di Tahap 15 x
    //   laporan.index     → hapus baris ini di Tahap 16 x
    //   pengguna.index    → hapus baris ini di Tahap 17 x
    // ════════════════════════════════════════════════════════════════════
    
    Route::get('/hutang', fn() => 'Halaman Hutang — akan dibuat di Tahap 15')->name('hutang.index');
    // aku kucing
});