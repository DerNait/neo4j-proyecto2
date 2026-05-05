<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RelationController;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Gestión de Nodos ───────────────────────────────────────────────────────────
Route::prefix('nodes')->name('nodes.')->group(function () {
    Route::get('/',           [NodeController::class, 'index'])->name('index');
    Route::get('/create',     [NodeController::class, 'create'])->name('create');
    Route::post('/',          [NodeController::class, 'store'])->name('store');
    Route::get('/aggregates', [NodeController::class, 'aggregates'])->name('aggregates');
    Route::get('/list',       [NodeController::class, 'list'])->name('list');
    Route::get('/detail',     [NodeController::class, 'show'])->name('show');
    Route::post('/delete',      [NodeController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-delete', [NodeController::class, 'bulkDestroy'])->name('bulk-destroy');
    Route::post('/import-csv',  [NodeController::class, 'importCsv'])->name('import-csv');
});

// ── Gestión de Propiedades de Nodos ───────────────────────────────────────────
Route::prefix('nodes/properties')->name('nodes.properties.')->group(function () {
    Route::post('/update',       [PropertyController::class, 'updateNode'])->name('update');
    Route::post('/remove',       [PropertyController::class, 'removeFromNode'])->name('remove');
    Route::post('/bulk-update',  [PropertyController::class, 'bulkUpdateNodes'])->name('bulk-update');
    Route::post('/bulk-remove',  [PropertyController::class, 'bulkRemoveFromNodes'])->name('bulk-remove');
});

// ── Gestión de Relaciones ──────────────────────────────────────────────────────
Route::prefix('relations')->name('relations.')->group(function () {
    // Páginas Inertia
    Route::get('/',       [RelationController::class, 'index'])->name('index');
    Route::get('/create', [RelationController::class, 'create'])->name('create');
    // API JSON
    Route::get('/list',   [RelationController::class, 'list'])->name('list');
    Route::post('/',      [RelationController::class, 'store'])->name('store');
    // Gestión de propiedades de relaciones
    Route::post('/properties/update',      [RelationController::class, 'updateRelation'])->name('properties.update');
    Route::post('/properties/remove',      [RelationController::class, 'removeFromRelation'])->name('properties.remove');
    Route::post('/properties/bulk-update', [RelationController::class, 'bulkUpdateRelations'])->name('properties.bulk-update');
    Route::post('/properties/bulk-remove', [RelationController::class, 'bulkRemoveFromRelations'])->name('properties.bulk-remove');
    Route::post('/delete',      [RelationController::class, 'destroyRelation'])->name('destroy');
    Route::post('/bulk-delete', [RelationController::class, 'bulkDestroyRelations'])->name('bulk-destroy');
    Route::post('/import-csv',  [RelationController::class, 'importCsv'])->name('import-csv');
});

// ── Analytics / Data Science ──────────────────────────────────────────────────
Route::prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/',            [AnalyticsController::class, 'index'])->name('index');
    Route::get('/recommend',   [AnalyticsController::class, 'recommend'])->name('recommend');
    Route::get('/influencers', [AnalyticsController::class, 'influencers'])->name('influencers');
    Route::get('/trending',    [AnalyticsController::class, 'trending'])->name('trending');
});

require __DIR__.'/auth.php';
