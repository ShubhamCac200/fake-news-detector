<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\NewsCheckController;

Route::get('/', [NewsCheckController::class, 'index'])->name('news.index');
Route::post('/news/store', [NewsCheckController::class, 'store'])->name('news.store');



