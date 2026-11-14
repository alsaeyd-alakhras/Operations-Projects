<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\ColumnProfileController;
use App\Http\Controllers\FinancierController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KuwaitiCaseController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix'=> '',
    'middleware' => ['auth'],
], function () {
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');

    Route::get('kuwaiti-cases/filter-options', [KuwaitiCaseController::class, 'filterOptions'])->name('kuwaiti-cases.filterOptions');
    Route::post('kuwaiti-cases/import', [KuwaitiCaseController::class, 'import'])->name('kuwaiti-cases.import');
    Route::post('kuwaiti-cases/export', [KuwaitiCaseController::class, 'export'])->name('kuwaiti-cases.export');
    Route::post('kuwaiti-cases/archive', [KuwaitiCaseController::class, 'archive'])->name('kuwaiti-cases.archive');
    Route::post('kuwaiti-cases/{kuwaiti_case}/management', [KuwaitiCaseController::class, 'management'])->name('kuwaiti-cases.management');
    Route::post('kuwaiti-cases/{kuwaiti_case}/done', [KuwaitiCaseController::class, 'done'])->name('kuwaiti-cases.done');
    Route::resource('kuwaiti-cases', KuwaitiCaseController::class)->except(['show']);

    Route::get('records/archived', [RecordController::class, 'archived'])->name('records.archived');
    Route::get('records/filter-options', [RecordController::class, 'filterOptions'])->name('records.filterOptions');
    Route::post('records/archivedRow', [RecordController::class, 'archivedRow'])->name('records.archivedRow');
    Route::post('records/print', [RecordController::class, 'print'])->name('records.print');
    Route::post('records/import', [RecordController::class, 'import'])->name('records.import');
    Route::post('records/{record}/management', [RecordController::class, 'management'])->name('records.management');
    Route::post('records/export', [RecordController::class, 'export'])->name('records.export');

    Route::post('financiers/print', [FinancierController::class, 'print'])->name('financiers.print');
    Route::post('financiers/archive', [FinancierController::class, 'archive'])->name('financiers.archive');

    Route::get('logs', [LogsController::class, 'index'])->name('logs.index');


    Route::resources([
        'records' => RecordController::class,
        'financiers' => FinancierController::class,
        'users' => UserController::class,
        'branches' => BranchController::class,
        'column-profiles' => ColumnProfileController::class,
    ]);
});
