<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\LibraryController;

Route::post('/checkout', [LibraryController::class, 'checkout']);
Route::post('/checkin', [LibraryController::class, 'checkin']);
