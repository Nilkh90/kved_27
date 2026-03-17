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
Route::get('/catalog/section/{id}', [CatalogController::class, 'section'])->name('catalog.section');
Route::get('/catalog/division/{id}', [CatalogController::class, 'division'])->name('catalog.division');
Route::get('/catalog/group/{id}', [CatalogController::class, 'group'])->name('catalog.group');
Route::get('/catalog/class/{id}', [CatalogController::class, 'class'])->name('catalog.class');

Route::get('/catalog/{standard}', [CatalogController::class, 'byStandard']); // kved|nace
Route::get('/code/{standard}/{code}', [CodeController::class, 'show'])
    ->middleware('cacheResponse:1440')
    ->name('code.show');
Route::get('/info', [InfoController::class, 'index'])->name('info');
Route::get('/info/{slug}', [InfoController::class, 'article'])->name('info.article');

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index']);

// Админ (basic auth middleware)
Route::middleware(['web', 'auth.admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/import', [AdminController::class, 'import'])->name('admin.import');
    Route::get('/kved', [AdminController::class, 'kved'])->name('admin.kved');
    Route::get('/nace', [AdminController::class, 'nace'])->name('admin.nace');
    Route::get('/mappings', [AdminController::class, 'mappings'])->name('admin.mappings');
    Route::post('/run-import-kved', [AdminController::class, 'runImportKved'])->name('admin.run-import-kved');
});

