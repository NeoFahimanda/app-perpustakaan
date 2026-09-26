<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Resource Routes untuk aplikasi perpustakaan
Route::resource('books', BookController::class);
Route::resource('categories', CategoryController::class)->except(['show']);
Route::resource('members', MemberController::class);
Route::resource('loans', LoanController::class);

// Route kustom untuk pengembalian buku
Route::put('/loans/{id}/kembalikan', [LoanController::class, 'kembalikan'])
    ->name('loans.kembalikan');

// Tugas Pertemuan 2: Route Group dengan Prefix /admin
Route::prefix('admin')->group(function () {
    Route::get('/info', function () {
        return 'Halaman Informasi Admin Perpustakaan';
    })->name('admin.info');
});