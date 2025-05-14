<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BarangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('barang', BarangController::class);
    Route::resource('barang_masuk', BarangMasukController::class)->except(['create']);
    Route::get('barang_masuk/create/{id?}', [BarangMasukController::class, 'create'])->name('barang_masuk.create.from.barang');
    Route::put('/barang-masuk/{barangMasuk}/terima', [BarangMasukController::class, 'terima'])->name('barang_masuk.terima');

    Route::resource('supplier', SupplierController::class);
    Route::get('/api/supplier', function () {
        return response()->json(App\Models\Supplier::orderBy('nama')->get());
    })->name('api.supplier');

    // Add these routes to your routes/web.php file

    // Barang Keluar Routes
    Route::get('barang-keluar', [App\Http\Controllers\BarangKeluarController::class, 'index'])->name('barang-keluar.index');
    Route::get('barang-keluar/create', [App\Http\Controllers\BarangKeluarController::class, 'create'])->name('barang-keluar.create');
    Route::post('barang-keluar', [App\Http\Controllers\BarangKeluarController::class, 'store'])->name('barang-keluar.store');
    Route::get('barang-keluar/{barangKeluar}', [App\Http\Controllers\BarangKeluarController::class, 'show'])->name('barang-keluar.show');

    // Kasir Routes
    Route::get('kasir', [App\Http\Controllers\BarangKeluarController::class, 'kasir'])->name('kasir');
    Route::get('kasir/search-product', [App\Http\Controllers\BarangKeluarController::class, 'searchProduct'])->name('kasir.search-product');
    Route::post('kasir/save-transaction', [App\Http\Controllers\BarangKeluarController::class, 'saveTransaction'])->name('kasir.save-transaction');
    Route::get('get-barang', [App\Http\Controllers\BarangKeluarController::class, 'getBarang'])->name('get-barang');
});
