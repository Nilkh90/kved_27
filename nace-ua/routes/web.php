<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CodeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// Редирект для прямых ссылок на старые HTML-файлы КВЕД (вверху для приоритета)
Route::get('/{any}', function($any) {
    if (preg_match('/KVED10_([A-Z0-9_]+)\.html/i', $any, $matches)) {
        $code = str_replace('_', '.', $matches[1]);
        return redirect()->route('catalog.show_by_code', ['standard' => 'kved', 'code' => $code]);
    }
    abort(404);
})->where('any', '.*KVED10_.*\.html$');

// Публичные страницы
Route::get('/', [HomeController::class, 'index'])->name('home');
// Каталог
Route::get('/catalog', [CatalogController::class, 'indexDefault'])->name('catalog');

Route::prefix('catalog/{standard}')->where(['standard' => 'kved|nace'])->group(function () {
    Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/section-{code}', [CatalogController::class, 'section'])->name('catalog.section');
    Route::get('/{division_code}', [CatalogController::class, 'division'])->name('catalog.division')->where('division_code', '[0-9]+');
    Route::get('/{division_code}/{group_code}', [CatalogController::class, 'group'])->name('catalog.group');
    Route::get('/{division_code}/{group_code}/{class_code}', [CatalogController::class, 'class'])->name('catalog.class');

    // Универсальный роут для поиска по коду внутри каталога (для коротких ссылок типа 33.1 или M)
    Route::get('/{code}', [CatalogController::class, 'showByCode'])->name('catalog.show_by_code')->where('code', '.*');
});

// Редиректы для старых URL без префикса стандарта (по умолчанию kved)
Route::get('/catalog/{code}/{p1?}/{p2?}', function($code, $p1 = null, $p2 = null) {
    if ($p2) {
        return redirect()->route('catalog.class', [
            'standard' => 'kved', 
            'division_code' => $code, 
            'group_code' => $p1, 
            'class_code' => $p2
        ]);
    }
    if ($p1) {
        return redirect()->route('catalog.group', [
            'standard' => 'kved', 
            'division_code' => $code, 
            'group_code' => $p1
        ]);
    }
    return redirect()->route('catalog.show_by_code', ['standard' => 'kved', 'code' => $code]);
})->where('code', '.*');

Route::get('/code/{standard}/{code}', [CodeController::class, 'show'])
    ->middleware('cacheResponse:1440')
    ->name('code.show')
    ->where('code', '.*');
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

