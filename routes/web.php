<?php

use App\Http\Controllers\RowsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelImportController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', [RowsController::class, 'test']);
Route::get('/rows', [RowsController::class, 'index']);


Route::middleware('auth.basic')->group(function () {
    Route::get('/upload', function () {
        return view('upload');
    });
});

Route::post('/upload', [ExcelImportController::class, 'upload'])->name('upload.handle');
