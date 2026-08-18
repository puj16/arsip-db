<?php

use App\Http\Controllers\ArsipController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

// Halaman utama: search bar + tabel metadata arsip (menerima ?q=keyword)
Route::get('/', [ArsipController::class, 'index'])->name('arsip.index');
Route::get('/search/live', [ArsipController::class, 'liveSearch'])->name('search.live');

// Tambah arsip (form metadata + upload dokumen opsional sekaligus)
Route::get('/arsip/tambah', [ArsipController::class, 'create'])->name('arsip.create');
Route::post('/arsip', [ArsipController::class, 'store'])->name('arsip.store');

// Dokumen: upload terpisah (kalau arsip sudah ada duluan), status polling, reprocess, lihat file
Route::post('/arsip/{arsip}/documents', [DocumentController::class, 'store'])->name('documents.store');
Route::get('/documents/{document}/status', [DocumentController::class, 'status'])->name('documents.status');
Route::post('/documents/{document}/reprocess', [DocumentController::class, 'reprocess'])->name('documents.reprocess');
Route::get('/documents/{document}/view', [DocumentController::class, 'view'])->name('documents.view');
