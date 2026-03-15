<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CodeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// Публичные страницы
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog');
Route::get('/catalog/{standard}', [CatalogController::class, 'byStandard']); // kved|nace
Route::get('/code/{standard}/{code}', [CodeController::class, 'show'])->name('code.show');
Route::get('/info', [InfoController::class, 'index'])->name('info');
Route::get('/info/{slug}', [InfoController::class, 'article'])->name('info.article');

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index']);

// Админ (позже будет auth.admin middleware)
Route::middleware('web')->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard']);
    Route::get('/import', [AdminController::class, 'import']);
});

