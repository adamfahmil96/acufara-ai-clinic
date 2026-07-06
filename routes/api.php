<?php

use App\Http\Controllers\Api\FonnteCheckController;
use Illuminate\Support\Facades\Route;

Route::prefix('fonnte')->group(function () {
    Route::post('/check', FonnteCheckController::class)
        ->middleware('throttle:10,1')
        ->name('api.fonnte.check');
});
