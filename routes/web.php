<?php

use App\Http\Controllers\RouterAnalysisController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RouterAnalysisController::class, 'index'])->name('dashboard');
Route::post('/analyze-file', [RouterAnalysisController::class, 'analyze'])->name('rsc.analyze');
Route::post('/analyze-api', [RouterAnalysisController::class, 'analyzeFromApi'])->name('api.analyze');
Route::post('/apply-fix', [RouterAnalysisController::class, 'applyFix'])->name('api.apply');