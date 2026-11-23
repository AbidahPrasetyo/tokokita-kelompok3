<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EtalaseController;

Route::get('/tes-etalase', [EtalaseController::class, 'index']);
